<?php

namespace Unzer\Core\Infrastructure\TaskExecution;

use Unzer\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Unzer\Core\Infrastructure\Logger\Logger;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Unzer\Core\Infrastructure\Utility\GuidProvider;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use Exception;

/**
 * Class TaskRunnerWakeupService.
 *
 * @package Unzer\Core\Infrastructure\TaskExecution
 */
class TaskRunnerWakeupService implements TaskRunnerWakeup
{
    /**
     * Service instance.
     *
     * @var ?AsyncProcessStarterService
     */
    private ?AsyncProcessStarterService $asyncProcessStarter = null;

    /**
     * Service instance.
     *
     * @var ?RunnerStatusStorage
     */
    private $runnerStatusStorage = null;

    /**
     * Service instance.
     *
     * @var ?TimeProvider
     */
    private ?TimeProvider $timeProvider = null;

    /**
     * Service instance.
     *
     * @var ?GuidProvider
     */
    private ?GuidProvider $guidProvider = null;

    /**
     * Wakes up @see TaskRunner instance asynchronously if active instance is not already running.
     */
    public function wakeup()
    {
        try {
            $this->doWakeup();
        } catch (TaskRunnerStatusChangeException $ex) {
            Logger::logDebug(
                'Fail to wakeup task runner. Runner status storage failed to set new active state.',
                'Core',
                [
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                ]
            );
        } catch (TaskRunnerStatusStorageUnavailableException $ex) {
            Logger::logDebug(
                'Fail to wakeup task runner. Runner status storage unavailable.',
                'Core',
                [
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                ]
            );
        } catch (Exception $ex) {
            Logger::logDebug(
                'Fail to wakeup task runner. Unexpected error occurred.',
                'Core',
                [
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                ]
            );
        }
    }

    /**
     * Executes wakeup of queued task.
     *
     * @throws ProcessStarterSaveException
     * @throws TaskRunnerStatusChangeException
     * @throws TaskRunnerStatusStorageUnavailableException
     * @throws HttpRequestException
     */
    private function doWakeup()
    {
        $runnerStatus = $this->getRunnerStorage()->getStatus();
        $currentGuid = $runnerStatus->getGuid();
        if (!empty($currentGuid) && !$runnerStatus->isExpired()) {
            return;
        }

        if ($runnerStatus->isExpired()) {
            $this->runnerStatusStorage->setStatus(TaskRunnerStatus::createNullStatus());
            Logger::logDebug('Expired task runner detected, wakeup component will start new instance.');
        }

        $guid = $this->getGuidProvider()->generateGuid();

        $this->runnerStatusStorage->setStatus(
            new TaskRunnerStatus(
                $guid,
                $this->getTimeProvider()->getCurrentLocalTime()->getTimestamp()
            )
        );

        $this->getAsyncProcessStarter()->start(new TaskRunnerStarter($guid));
    }

    /**
     * Gets instance of @return TaskRunnerStatusStorage Service instance.
     * @see TaskRunnerStatusStorageInterface.
     *
     */
    private function getRunnerStorage()
    {
        if ($this->runnerStatusStorage === null) {
            $this->runnerStatusStorage = ServiceRegister::getService(TaskRunnerStatusStorage::CLASS_NAME);
        }

        return $this->runnerStatusStorage;
    }

    /**
     * Gets instance of @return GuidProvider Service instance.
     * @see GuidProvider.
     *
     */
    private function getGuidProvider(): GuidProvider
    {
        if ($this->guidProvider === null) {
            $this->guidProvider = ServiceRegister::getService(GuidProvider::CLASS_NAME);
        }

        return $this->guidProvider;
    }

    /**
     * Gets instance of @return TimeProvider Service instance.
     * @see TimeProvider.
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
     * Gets instance of @return AsyncProcessStarterService Service instance.
     * @see AsyncProcessStarterService.
     *
     */
    private function getAsyncProcessStarter(): AsyncProcessStarterService
    {
        if ($this->asyncProcessStarter === null) {
            $this->asyncProcessStarter = ServiceRegister::getService(AsyncProcessService::CLASS_NAME);
        }

        return $this->asyncProcessStarter;
    }
}
