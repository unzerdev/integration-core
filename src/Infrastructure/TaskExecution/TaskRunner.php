<?php

namespace Unzer\Core\Infrastructure\TaskExecution;

use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\Configuration\ConfigurationManager;
use Unzer\Core\Infrastructure\Logger\Logger;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use Exception;

/**
 * Class TaskRunner.
 *
 * @package Unzer\Core\Infrastructure\TaskExecution
 */
class TaskRunner
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Automatic task runner wakeup delay in seconds
     */
    const WAKEUP_DELAY = 5;

    /**
     * Defines minimal time in seconds between two consecutive alive since updates.
     */
    const TASK_RUNNER_KEEP_ALIVE_PERIOD = 2;

    /**
     * Runner guid.
     *
     * @var string
     */
    protected string $guid;

    /**
     * Service.
     *
     * @var ?QueueService
     */
    private ?QueueService $queueService = null;

    /**
     * Service.
     *
     * @var ?TaskRunnerStatusStorage
     */
    private ?TaskRunnerStatusStorage $runnerStorage = null;

    /**
     * Service.
     *
     * @var ?Configuration
     */
    private ?Configuration $configurationService = null;

    /**
     * Service.
     *
     * @var ?TimeProvider
     */
    private ?TimeProvider $timeProvider = null;

    /**
     * Service.
     *
     * @var ?TaskRunnerWakeup
     */
    private ?TaskRunnerWakeup $taskWakeup = null;

    /**
     * Configuration manager.
     *
     * @var ?ConfigurationManager Configuration manager instance.
     */
    private ?ConfigurationManager $configurationManager = null;

    /**
     * Defines when was the task runner alive since time step last updated at.
     *
     * @var int
     */
    private int $aliveSinceUpdatedAt = 0;

    /**
     * Sleep time in seconds with microsecond precision.
     *
     * @var float
     */
    private float $batchSleepTime = 0.0;

    /**
     * Sets task runner guid.
     *
     * @param string $guid Runner guid to set.
     */
    public function setGuid(string $guid): void
    {
        $this->guid = $guid;
    }

    /**
     * Starts task runner lifecycle.
     */
    public function run()
    {
        try {
            $this->keepAlive();

            if ($this->getConfigurationService()->isTaskRunnerHalted()) {
                $this->logDebug(['Message' => 'Task runner is currently halted.']);
                return;
            }

            $this->logDebug(['Message' => 'Task runner: lifecycle started.']);

            if ($this->isCurrentRunnerAlive()) {
                $this->failOrRequeueExpiredTasks();
                $this->startOldestQueuedItems();
            }

            $this->keepAlive();

            $this->wakeup();

            $this->logDebug(['Message' => 'Task runner: lifecycle ended.']);
        } catch (Exception $ex) {
            $this->logDebug(
                [
                    'Message' => 'Fail to run task runner. Unexpected error occurred.',
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                ]
            );
        }
    }

    /**
     * Fails or re-queues expired tasks.
     *
     * @throws QueueItemDeserializationException
     * @throws QueueStorageUnavailableException
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    private function failOrRequeueExpiredTasks()
    {
        $this->logDebug(['Message' => 'Task runner: expired tasks cleanup started.']);

        $runningItems = $this->getQueue()->findRunningItems();
        if (!$this->isCurrentRunnerAlive()) {
            return;
        }

        $this->keepAlive();

        while ($runningItem = array_shift($runningItems)) {
            if (!$this->isCurrentRunnerAlive()) {
                break;
            }

            try {
                $this->failOrRequeueExpiredTask($runningItem);
            } catch (QueueItemDeserializationException $e) {
                // If task can't be deserialized we should fail it immediately since there is nothing we can dao to recover
                $this->getQueue()->fail(
                    $runningItem,
                    sprintf('Task %s is invalid or corrupted. Task deserialization failed.',
                        $runningItem->getId()
                    ),
                    true
                );
            }

            $this->keepAlive();
        }
    }

    /**
     * Fails or re-queues provided task if it expired.
     *
     * @param QueueItem $item
     *
     * @throws QueueItemDeserializationException
     * @throws QueueStorageUnavailableException
     */
    private function failOrRequeueExpiredTask(QueueItem $item)
    {
        if (!$this->isItemExpired($item)) {
            return;
        }

        $this->logMessageFor($item, 'Task runner: Expired task detected.');
        $this->getConfigurationManager()->setContext($item->getContext());
        if ($item->getProgressBasePoints() > $item->getLastExecutionProgressBasePoints()) {
            $this->logMessageFor($item, 'Task runner: Task requeue for execution continuation.');
            $this->getQueue()->requeue($item);
        } else {
            $item->reconfigureTask();
            $this->getQueue()->fail(
                $item,
                sprintf(
                    'Task %s failed due to extended inactivity period.',
                    $this->getItemDescription($item)
                )
            );
        }
    }

    /**
     * Starts oldest queue item from all queues respecting following list of criteria:
     *      - Queue must be without already running queue items
     *      - For one queue only one (oldest queued) item should be started
     *      - Number of running tasks must NOT be greater than maximal allowed by integration configuration.
     *
     * @throws ProcessStarterSaveException
     * @throws QueueItemDeserializationException
     * @throws TaskRunnerStatusStorageUnavailableException
     * @throws QueryFilterInvalidParamException
     */
    private function startOldestQueuedItems()
    {
        $this->keepAlive();

        $this->logDebug(['Message' => 'Task runner: available task detection started.']);

        // Calculate how many queue items can be started
        $maxRunningTasks = $this->getConfigurationService()->getMaxStartedTasksLimit();
        $alreadyRunningItems = $this->getQueue()->findRunningItems();
        $numberOfAvailableSlots = $maxRunningTasks - count($alreadyRunningItems);
        if ($numberOfAvailableSlots <= 0) {
            $this->logDebug(['Message' => 'Task runner: max number of active tasks reached.']);

            return;
        }

        $this->keepAlive();

        $items = $this->getQueue()->findOldestQueuedItems($numberOfAvailableSlots);

        $this->keepAlive();

        if (!$this->isCurrentRunnerAlive()) {
            return;
        }

        $asyncStarterBatchSize = $this->getConfigurationService()->getAsyncStarterBatchSize();
        $batchStarter = new AsyncBatchStarter($asyncStarterBatchSize);
        foreach ($items as $item) {
            $this->logMessageFor($item, 'Task runner: Adding task to a batch starter for async execution.');
            $batchStarter->addRunner(new QueueItemStarter($item->getId()));
        }

        $this->keepAlive();

        if (!$this->isCurrentRunnerAlive()) {
            return;
        }

        $this->logDebug(['Message' => 'Task runner: Starting batch starter execution.']);
        $startTime = $this->getTimeProvider()->getMicroTimestamp();
        $batchStarter->run();
        $endTime = $this->getTimeProvider()->getMicroTimestamp();

        $this->keepAlive();

        $averageRequestTime = ($endTime - $startTime) / $asyncStarterBatchSize;
        $this->batchSleepTime = $batchStarter->getWaitTime($averageRequestTime);

        $this->logDebug(
            [
                'Message' => 'Task runner: Batch starter execution finished.',
                'ExecutionTime' => ($endTime - $startTime) . 's',
                'AverageRequestTime' => $averageRequestTime . 's',
                'StartedItems' => count($items),
            ]
        );
    }

    /**
     * Executes wakeup on runner.
     *
     * @throws TaskRunnerStatusChangeException
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    private function wakeup()
    {
        $this->logDebug(['Message' => 'Task runner: starting self deactivation.']);

        for ($i = 0; $i < $this->getWakeupDelay(); $i++) {
            $this->getTimeProvider()->sleep(1);
            $this->keepAlive();
        }

        $this->getRunnerStorage()->setStatus(TaskRunnerStatus::createNullStatus());

        $this->logDebug(['Message' => 'Task runner: sending task runner wakeup signal.']);
        $this->getTaskWakeup()->wakeup();
    }

    /**
     * Updates alive since time stamp of the task runner.
     *
     * @throws TaskRunnerStatusStorageUnavailableException
     * @throws QueryFilterInvalidParamException
     */
    private function keepAlive()
    {
        $currentTime = $this->getTimeProvider()->getCurrentLocalTime()->getTimestamp();
        if (($currentTime - $this->aliveSinceUpdatedAt) < self::TASK_RUNNER_KEEP_ALIVE_PERIOD) {

            return;
        }

        $this->getConfigurationService()->setTaskRunnerStatus($this->guid, $currentTime);
        $this->aliveSinceUpdatedAt = $currentTime;
    }

    /**
     * Checks whether current runner is alive.
     *
     * @return bool TRUE if runner is alive; otherwise, FALSE.
     *
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    private function isCurrentRunnerAlive(): bool
    {
        $runnerStatus = $this->getRunnerStorage()->getStatus();
        $runnerExpired = $runnerStatus->isExpired();
        $runnerGuidIsCorrect = $this->guid === $runnerStatus->getGuid();

        if ($runnerExpired) {
            $this->logWarning(['Message' => 'Task runner: Task runner started but it is expired.']);
        }

        if (!$runnerGuidIsCorrect) {
            $this->logWarning(['Message' => 'Task runner: Task runner started but it is not active anymore.']);
        }

        return !$runnerExpired && $runnerGuidIsCorrect;
    }

    /**
     * Checks whether queue item is expired.
     *
     * @param QueueItem $item Queue item for checking.
     *
     * @return bool TRUE if queue item expired; otherwise, FALSE.
     *
     * @throws QueueItemDeserializationException
     */
    private function isItemExpired(QueueItem $item): bool
    {
        $currentTimestamp = $this->getTimeProvider()->getCurrentLocalTime()->getTimestamp();
        $maxTaskInactivityPeriod = $item->getTask()->getMaxInactivityPeriod();

        return ($item->getLastUpdateTimestamp() + $maxTaskInactivityPeriod) < $currentTimestamp;
    }

    /**
     * Gets queue item description.
     *
     * @param QueueItem $item Queue item.
     *
     * @return string Description of queue item.
     *
     * @throws QueueItemDeserializationException
     */
    private function getItemDescription(QueueItem $item): string
    {
        return "{$item->getId()}({$item->getTaskType()})";
    }

    /**
     * Gets @return QueueService Queue service instance.
     * @see QueueService service instance.
     *
     */
    private function getQueue(): QueueService
    {
        if ($this->queueService === null) {
            $this->queueService = ServiceRegister::getService(QueueService::CLASS_NAME);
        }

        return $this->queueService;
    }

    /**
     * Gets @return TaskRunnerStatusStorage Service instance.
     * @see TaskRunnerStatusStorageInterface service instance.
     *
     */
    private function getRunnerStorage(): TaskRunnerStatusStorage
    {
        if ($this->runnerStorage === null) {
            $this->runnerStorage = ServiceRegister::getService(TaskRunnerStatusStorage::CLASS_NAME);
        }

        return $this->runnerStorage;
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager Configuration manager instance.
     */
    public function getConfigurationManager(): ConfigurationManager
    {
        if ($this->configurationManager === null) {
            $this->configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);
        }

        return $this->configurationManager;
    }

    /**
     * Gets @return Configuration Service instance.
     * @see Configuration service instance.
     *
     */
    private function getConfigurationService(): Configuration
    {
        if ($this->configurationService === null) {
            $this->configurationService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configurationService;
    }

    /**
     * Gets @return TimeProvider Service instance.
     * @see TimeProvider instance.
     *
     */
    private function getTimeProvider(): TimeProvider
    {
        if ($this->timeProvider === null) {
            $this->timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
        }

        return $this->timeProvider;
    }

    /**
     * Gets @return TaskRunnerWakeup Service instance.
     * @see TaskRunnerWakeupInterface service instance.
     *
     */
    private function getTaskWakeup(): TaskRunnerWakeup
    {
        if ($this->taskWakeup === null) {
            $this->taskWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        }

        return $this->taskWakeup;
    }

    /**
     * Returns wakeup delay in seconds
     *
     * @return int Wakeup delay in seconds.
     *
     * @throws QueryFilterInvalidParamException
     */
    private function getWakeupDelay(): int
    {
        $configurationValue = $this->getConfigurationService()->getTaskRunnerWakeupDelay();

        $minimalSleepTime = $configurationValue !== null ? $configurationValue : self::WAKEUP_DELAY;

        return $minimalSleepTime + ceil($this->batchSleepTime);
    }

    /**
     * Logs message and queue item details.
     *
     * @param QueueItem $queueItem Queue item.
     * @param string $message Message to be logged.
     *
     * @throws QueueItemDeserializationException
     */
    private function logMessageFor(QueueItem $queueItem, string $message)
    {
        $this->logDebug(
            [
                'RunnerGuid' => $this->guid,
                'Message' => $message,
                'TaskId' => $queueItem->getId(),
                'TaskType' => $queueItem->getTaskType(),
                'TaskRetries' => $queueItem->getRetries(),
                'TaskProgressBasePoints' => $queueItem->getProgressBasePoints(),
                'TaskLastExecutionProgressBasePoints' => $queueItem->getLastExecutionProgressBasePoints(),
            ]
        );
    }

    /**
     * Helper methods to encapsulate debug level logging.
     *
     * @param array $debugContent Array of debug content for logging.
     */
    private function logDebug(array $debugContent)
    {
        $debugContent['RunnerGuid'] = $this->guid;
        Logger::logDebug($debugContent['Message'], 'Core', $debugContent);
    }

    /**
     * Helper methods to encapsulate warning level logging.
     *
     * @param array $debugContent Array of debug content for logging.
     */
    private function logWarning(array $debugContent)
    {
        $debugContent['RunnerGuid'] = $this->guid;
        Logger::logWarning($debugContent['Message'], 'Core', $debugContent);
    }
}
