<?php

namespace Unzer\Core\Infrastructure\TaskExecution;

use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;

/**
 * Class RunnerStatusStorage.
 *
 * @package Unzer\Core\Infrastructure\TaskExecution
 */
class RunnerStatusStorage implements TaskRunnerStatusStorage
{
    /**
     * Configuration service instance.
     *
     * @var Configuration
     */
    private ?Configuration $configService = null;

    /**
     * Returns task runner status.
     *
     * @return TaskRunnerStatus Task runner status instance.
     *
     * @throws TaskRunnerStatusStorageUnavailableException
     * @throws QueryFilterInvalidParamException
     */
    public function getStatus(): TaskRunnerStatus
    {
        $result = $this->getConfigService()->getTaskRunnerStatus();
        if (empty($result)) {
            $this->getConfigService()->setTaskRunnerStatus('', null);

            return TaskRunnerStatus::createNullStatus();
        }

        return new TaskRunnerStatus($result['guid'], $result['timestamp']);
    }

    /**
     * Sets task runner status.
     *
     * @param TaskRunnerStatus $status Status instance.
     *
     * @throws TaskRunnerStatusChangeException
     * @throws TaskRunnerStatusStorageUnavailableException
     * @throws QueryFilterInvalidParamException
     */
    public function setStatus(TaskRunnerStatus $status)
    {
        $this->checkTaskRunnerStatusChangeAvailability($status);
        $this->getConfigService()->setTaskRunnerStatus($status->getGuid(), $status->getAliveSinceTimestamp());
    }

    /**
     * Checks if task runner can change availability status.
     *
     * @param TaskRunnerStatus $status Status instance.
     *
     * @throws TaskRunnerStatusChangeException
     * @throws TaskRunnerStatusStorageUnavailableException
     * @throws QueryFilterInvalidParamException
     */
    private function checkTaskRunnerStatusChangeAvailability(TaskRunnerStatus $status)
    {
        $currentGuid = $this->getStatus()->getGuid();
        $guidForUpdate = $status->getGuid();

        if (!empty($currentGuid) && !empty($guidForUpdate) && $currentGuid !== $guidForUpdate) {
            throw new TaskRunnerStatusChangeException(
                'Task runner with guid: ' . $guidForUpdate . ' can not change the status.'
            );
        }
    }

    /**
     * Gets instance of @return Configuration Service instance.
     * @see Configuration service.
     *
     */
    private function getConfigService(): Configuration
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
