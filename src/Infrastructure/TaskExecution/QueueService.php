<?php

namespace Unzer\Core\Infrastructure\TaskExecution;

use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Unzer\Core\Infrastructure\ORM\RepositoryRegistry;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\Events\BeforeQueueStatusChangeEvent;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueItemAbortedEvent;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueItemCreatedEvent;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueItemEnqueuedEvent;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueItemFailedEvent;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueItemFinishedEvent;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueItemRequeuedEvent;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueItemStartedEvent;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueStatusChangedEvent;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Unzer\Core\Infrastructure\Utility\Events\Event;
use Unzer\Core\Infrastructure\Utility\Events\EventBus;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use BadMethodCallException;
use RuntimeException;

/**
 * Class Queue.
 *
 * @package Unzer\Core\Infrastructure\TaskExecution
 */
class QueueService
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Maximum failure retries count
     */
    const MAX_RETRIES = 5;

    /**
     * A storage for task queue.
     *
     * @var ?QueueItemRepository
     */
    private ?QueueItemRepository $storage = null;

    /**
     * Time provider instance.
     *
     * @var ?TimeProvider
     */
    private ?TimeProvider $timeProvider = null;

    /**
     * Task runner wakeup instance.
     *
     * @var ?TaskRunnerWakeup
     */
    private ?TaskRunnerWakeup $taskRunnerWakeup = null;

    /**
     * Configuration service instance.
     *
     * @var ?Configuration
     */
    private ?Configuration $configService = null;

    /**
     * Updates status of a group of tasks.
     *
     * @param array $ids
     * @param string $status
     */
    public function batchStatusUpdate(array $ids, string $status)
    {
        $this->getStorage()->batchStatusUpdate($ids, $status);
    }

    /**
     * Creates queue item.
     *
     * @param $queueName
     * @param Task $task
     * @param string $context
     * @param int $priority
     * @param int|null $parent
     *
     * @return QueueItem
     *
     * @throws QueueStorageUnavailableException
     */
    public function create(
        $queueName,
        Task $task,
        string $context = '',
        int $priority = Priority::NORMAL,
        ?int $parent = null
    ): QueueItem {
        $queueItem = $this->instantiate($task, $queueName, $context, $priority, $parent);
        $this->save($queueItem);
        $this->fireStateTransitionEvent(new QueueItemCreatedEvent($queueItem));

        return $queueItem;
    }

    /**
     * Enqueues queue item to a given queue and stores changes.
     *
     * @param string $queueName Name of a queue where queue item should be queued.
     * @param Task $task Task to enqueue.
     * @param string $context Task execution context. If integration supports multiple accounts (middleware
     *     integration) context based on account id should be provided. Failing to do this will result in global task
     *     context and unpredictable task execution.
     *
     * @param int $priority
     *
     * @return QueueItem Created queue item.
     *
     * @throws QueueStorageUnavailableException When queue storage fails to save the item.
     */
    public function enqueue(
        string $queueName,
        Task $task,
        string $context = '',
        int $priority = Priority::NORMAL
    ): QueueItem {
        $queueItem = $this->instantiate($task, $queueName, $context, $priority);
        $queueItem->setStatus(QueueItem::QUEUED);
        $queueItem->setQueueTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $this->save($queueItem, [], true, QueueItem::CREATED);
        $this->fireStateTransitionEvent(new QueueItemEnqueuedEvent($queueItem));

        $this->getTaskRunnerWakeup()->wakeup();

        return $queueItem;
    }

    /**
     * Validates that the execution requirements are met for the particular
     * Execution job.
     *
     * @param QueueItem $queueItem
     */
    public function validateExecutionRequirements(QueueItem $queueItem)
    {
    }

    /**
     * Starts task execution, puts queue item in "in_progress" state and stores queue item changes.
     *
     * @param QueueItem $queueItem Queue item to start.
     *
     * @throws QueueItemDeserializationException
     * @throws QueueStorageUnavailableException
     * @throws AbortTaskExecutionException
     */
    public function start(QueueItem $queueItem)
    {
        if ($queueItem->getStatus() !== QueueItem::QUEUED) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::IN_PROGRESS);
        }

        $lastUpdateTimestamp = $queueItem->getLastUpdateTimestamp();

        $queueItem->setStatus(QueueItem::IN_PROGRESS);
        $queueItem->setStartTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $queueItem->setLastUpdateTimestamp($queueItem->getStartTimestamp());

        $this->save(
            $queueItem,
            ['status' => QueueItem::QUEUED, 'lastUpdateTimestamp' => $lastUpdateTimestamp],
            true,
            QueueItem::QUEUED
        );

        if ($queueItem->getTask() === null) {
            throw new QueueItemDeserializationException('Deserialized task is null.');
        }

        $this->fireStateTransitionEvent(new QueueItemStartedEvent($queueItem));
        $queueItem->getTask()->execute();
    }

    /**
     * Puts queue item in finished status and stores changes.
     *
     * @param QueueItem $queueItem Queue item to finish.
     *
     * @throws QueueStorageUnavailableException
     */
    public function finish(QueueItem $queueItem)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::COMPLETED);
        }

        $queueItem->setStatus(QueueItem::COMPLETED);
        $queueItem->setFinishTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $queueItem->setProgressBasePoints(10000);

        $this->save(
            $queueItem,
            ['status' => QueueItem::IN_PROGRESS, 'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp()],
            true,
            QueueItem::IN_PROGRESS
        );

        $this->fireStateTransitionEvent(new QueueItemFinishedEvent($queueItem));
    }

    /**
     * Returns queue item back to queue and sets updates last execution progress to current progress value.
     *
     * @param QueueItem $queueItem Queue item to requeue.
     *
     * @throws QueueStorageUnavailableException
     */
    public function requeue(QueueItem $queueItem)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::QUEUED);
        }

        $lastExecutionProgress = $queueItem->getLastExecutionProgressBasePoints();

        $queueItem->setStatus(QueueItem::QUEUED);
        $queueItem->setStartTimestamp(null);
        $queueItem->setLastExecutionProgressBasePoints($queueItem->getProgressBasePoints());

        $this->save(
            $queueItem,
            [
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $lastExecutionProgress,
                'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
            ],
            true,
            QueueItem::IN_PROGRESS
        );

        $this->fireStateTransitionEvent(new QueueItemRequeuedEvent($queueItem));
    }

    /**
     * Returns queue item back to queue and increments retries count.
     * When max retries count is reached puts item in failed status.
     *
     * @param QueueItem $queueItem Queue item to fail.
     * @param string $failureDescription Verbal description of failure.
     * @param bool $force Ignores max retries and forces failure.
     *
     * @throws QueueItemDeserializationException
     * @throws QueueStorageUnavailableException
     */
    public function fail(QueueItem $queueItem, string $failureDescription, bool $force = false)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::FAILED);
        }

        $task = null;
        try {
            $task = $queueItem->getTask();
        } catch (\Exception $e) {
        }

        if (!$force && $task === null) {
            throw new QueueItemDeserializationException("Failed to deserialize task.");
        }

        $queueItem->setRetries($queueItem->getRetries() + 1);
        $queueItem->setFailureDescription(
            ($queueItem->getFailureDescription() ? ($queueItem->getFailureDescription() . "\n") : '')
            . 'Attempt ' . $queueItem->getRetries() . ': ' . $failureDescription
        );

        if ($force || $queueItem->getRetries() > $this->getMaxRetries()) {
            $queueItem->setStatus(QueueItem::FAILED);
            $queueItem->setFailTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
            if ($task !== null) {
                $task->onFail();
            }
        } else {
            $queueItem->setStatus(QueueItem::QUEUED);
            $queueItem->setStartTimestamp(null);
        }

        $this->save(
            $queueItem,
            [
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $queueItem->getLastExecutionProgressBasePoints(),
                'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
            ],
            true,
            QueueItem::IN_PROGRESS
        );

        if ($queueItem->getStatus() === QueueItem::FAILED && $queueItem->getParentId()) {
            $parent = $this->find($queueItem->getParentId());
            if ($parent === null) {
                throw new RuntimeException("Parent not found");
            }

            $this->fail($parent, "SubJob failed.", true);
        }

        $this->fireStateTransitionEvent(new QueueItemFailedEvent($queueItem, $failureDescription));
    }

    /**
     * Aborts the queue item. Aborted queue item will not be started again.
     *
     * @param QueueItem $queueItem Queue item to abort.
     * @param string $abortDescription Verbal description of the reason for abortion.
     *
     * @throws BadMethodCallException Queue item must be in "in_progress" status for abort method.
     * @throws QueueStorageUnavailableException
     * @throws QueueItemDeserializationException
     */
    public function abort(QueueItem $queueItem, string $abortDescription)
    {
        if (!in_array($queueItem->getStatus(), [QueueItem::CREATED, QueueItem::QUEUED, QueueItem::IN_PROGRESS])) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::ABORTED);
        }

        $task = $queueItem->getTask();
        if ($task === null) {
            throw new QueueItemDeserializationException("Failed to deserialize task.");
        }

        $task->onAbort();

        $queueItem->setStatus(QueueItem::ABORTED);
        $queueItem->setFailTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $queueItem->setFailureDescription(
            ($queueItem->getFailureDescription() ? ($queueItem->getFailureDescription() . "\n") : '')
            . 'Attempt ' . ($queueItem->getRetries() + 1) . ': ' . $abortDescription
        );
        $this->save(
            $queueItem,
            [
                'lastExecutionProgress' => $queueItem->getLastExecutionProgressBasePoints(),
                'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
            ],
            true,
            $queueItem->getStatus()
        );


        if ($queueItem->getParentId()) {
            $parent = $this->find($queueItem->getParentId());
            if ($parent === null) {
                throw new RuntimeException("Parent not found");
            }

            $this->abort($parent, "SubJob aborted.");
        }

        $this->fireStateTransitionEvent(new QueueItemAbortedEvent($queueItem, $abortDescription));
    }

    /**
     * Updates queue item progress.
     *
     * @param QueueItem $queueItem Queue item to be updated.
     * @param int $progress New progress.
     *
     * @throws QueueStorageUnavailableException
     */
    public function updateProgress(QueueItem $queueItem, int $progress)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            throw new BadMethodCallException('Progress reported for not started queue item.');
        }

        if ($progress === 10000) {
            $this->finish($queueItem);
            return;
        }

        $lastExecutionProgress = $queueItem->getLastExecutionProgressBasePoints();
        $lastUpdateTimestamp = $queueItem->getLastUpdateTimestamp();

        $queueItem->setProgressBasePoints($progress);
        $queueItem->setLastUpdateTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());

        $this->save(
            $queueItem,
            [
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $lastExecutionProgress,
                'lastUpdateTimestamp' => $lastUpdateTimestamp,
            ]
        );
    }

    /**
     * Keeps passed queue item alive by setting last update timestamp.
     *
     * @param QueueItem $queueItem Queue item to keep alive.
     *
     * @throws QueueStorageUnavailableException
     */
    public function keepAlive(QueueItem $queueItem)
    {
        $lastExecutionProgress = $queueItem->getLastExecutionProgressBasePoints();
        $lastUpdateTimestamp = $queueItem->getLastUpdateTimestamp();
        $queueItem->setLastUpdateTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $this->save(
            $queueItem,
            [
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $lastExecutionProgress,
                'lastUpdateTimestamp' => $lastUpdateTimestamp,
            ]
        );
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Finds queue item by Id.
     *
     * @param int $id Id of a queue item to find.
     *
     * @return QueueItem|null Queue item if found; otherwise, NULL.
     */
    public function find(int $id): ?QueueItem
    {
        $filter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('id', '=', $id);

        return $this->getStorage()->selectOne($filter);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Finds latest queue item by type.
     *
     * @param string $type Type of a queue item to find.
     * @param string $context Task scope restriction, default is global scope.
     *
     * @return QueueItem|null Queue item if found; otherwise, NULL.
     */
    public function findLatestByType(string $type, string $context = ''): ?QueueItem
    {
        $filter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('taskType', '=', $type);
        if (!empty($context)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $filter->where('context', '=', $context);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->orderBy('queueTime', 'DESC');

        return $this->getStorage()->selectOne($filter);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Finds queue items with status "in_progress".
     *
     * @return QueueItem[] Running queue items.
     */
    public function findRunningItems(): array
    {
        $filter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('status', '=', QueueItem::IN_PROGRESS);

        return $this->getStorage()->select($filter);
    }

    /**
     * Finds list of earliest queued queue items per queue.
     * Only queues that doesn't have running tasks are taken in consideration.
     * Returned queue items are ordered in the descending priority.
     *
     * @param int $limit Result set limit. By default max 10 earliest queue items will be returned.
     *
     * @return QueueItem[] An array of found queue items.
     */
    public function findOldestQueuedItems(int $limit = 10): array
    {
        $result = [];
        $currentLimit = $limit;

        foreach (QueueItem::getAvailablePriorities() as $priority) {
            $batch = $this->getStorage()->findOldestQueuedItems($priority, $currentLimit);
            $result[] = $batch;

            if (($currentLimit -= count($batch)) <= 0) {
                break;
            }
        }

        $result = !empty($result) ? array_merge(...$result) : $result;

        return array_slice($result, 0, $limit);
    }

    /**
     * @param Event $event
     */
    public function fireStateTransitionEvent(Event $event)
    {
        $bus = ServiceRegister::getService(QueueItemStateTransitionEventBus::CLASS_NAME);
        $bus->fire($event);
    }

    /**
     * Creates or updates given queue item using storage service. If queue item id is not set, new queue item will be
     * created; otherwise, update will be performed.
     *
     * @param QueueItem $queueItem Item to save.
     * @param array $additionalWhere List of key/value pairs to set in where clause when saving queue item.
     * @param bool $reportStateChange Indicates whether to invoke a status change event.
     * @param string $previousState If event should be invoked, indicates the previous state.
     *
     * @return int Id of saved queue item.
     *
     * @throws QueueStorageUnavailableException
     */
    private function save(
        QueueItem $queueItem,
        array $additionalWhere = [],
        bool $reportStateChange = false,
        string $previousState = ''
    ): int {
        try {
            if ($reportStateChange) {
                $this->reportBeforeStatusChange($queueItem, $previousState);
            }

            $id = $this->getStorage()->saveWithCondition($queueItem, $additionalWhere);
            $queueItem->setId($id);

            if ($reportStateChange) {
                $this->reportStatusChange($queueItem, $previousState);
            }

            return $id;
        } catch (QueueItemSaveException $exception) {
            throw new QueueStorageUnavailableException('Unable to update the task.', $exception);
        }
    }

    /**
     * Fires event for before status change.
     *
     * @param QueueItem $queueItem Queue item with is about to change status.
     * @param string $previousState Previous state. MUST be one of the states defined as constants in @see QueueItem.
     */
    private function reportBeforeStatusChange(QueueItem $queueItem, string $previousState)
    {
        /** @var EventBus $eventBus */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);
        $eventBus->fire(new BeforeQueueStatusChangeEvent($queueItem, $previousState));
    }

    /**
     * Fires event for status change.
     *
     * @param QueueItem $queueItem Queue item with changed status.
     * @param string $previousState Previous state. MUST be one of the states defined as constants in @see QueueItem.
     */
    private function reportStatusChange(QueueItem $queueItem, string $previousState)
    {
        /** @var EventBus $eventBus */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);
        $eventBus->fire(new QueueStatusChangedEvent($queueItem, $previousState));
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Gets task storage instance.
     *
     * @return QueueItemRepository Task storage instance.
     */
    private function getStorage(): QueueItemRepository
    {
        if ($this->storage === null) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->storage = RepositoryRegistry::getQueueItemRepository();
        }

        return $this->storage;
    }

    /**
     * Gets time provider instance.
     *
     * @return TimeProvider Time provider instance.
     */
    private function getTimeProvider(): TimeProvider
    {
        if ($this->timeProvider === null) {
            $this->timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
        }

        return $this->timeProvider;
    }

    /**
     * Gets task runner wakeup instance.
     *
     * @return TaskRunnerWakeup Task runner wakeup instance.
     */
    private function getTaskRunnerWakeup(): TaskRunnerWakeup
    {
        if ($this->taskRunnerWakeup === null) {
            $this->taskRunnerWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        }

        return $this->taskRunnerWakeup;
    }

    /**
     * Gets configuration service instance.
     *
     * @return Configuration Configuration service instance.
     */
    private function getConfigService(): Configuration
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }

    /**
     * Prepares exception message and throws exception.
     *
     * @param string $fromStatus A status form which status change is attempts.
     * @param string $toStatus A status to which status change is attempts.
     *
     * @throws BadMethodCallException
     */
    private function throwIllegalTransitionException(string $fromStatus, string $toStatus)
    {
        throw new BadMethodCallException(
            sprintf(
                'Illegal queue item state transition from "%s" to "%s"',
                $fromStatus,
                $toStatus
            )
        );
    }

    /**
     * Returns maximum number of retries.
     *
     * @return int Number of retries.
     *
     * @throws QueryFilterInvalidParamException
     */
    private function getMaxRetries(): int
    {
        $configurationValue = $this->getConfigService()->getMaxTaskExecutionRetries();

        return $configurationValue !== null ? $configurationValue : self::MAX_RETRIES;
    }

    /**
     * Instantiates queue item for a task.
     *
     * @param Task $task
     * @param string $queueName
     * @param string $context
     * @param int $priority
     * @param int | null $parent
     *
     * @return QueueItem
     */
    private function instantiate(Task $task, string $queueName, string $context, int $priority, ?int $parent = null):
    QueueItem {
        $queueItem = new QueueItem($task);
        $queueItem->setQueueName($queueName);
        $queueItem->setContext($context);
        $queueItem->setPriority($priority);
        $queueItem->setStatus(QueueItem::CREATED);
        $queueItem->setParentId($parent);

        return $queueItem;
    }
}
