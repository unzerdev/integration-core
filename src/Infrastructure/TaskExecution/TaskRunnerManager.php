<?php

namespace Unzer\Core\Infrastructure\TaskExecution;

use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerManager as BaseService;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;

/**
 * Class TaskRunnerManager.
 *
 * @package Unzer\Core\Infrastructure\TaskExecution
 */
class TaskRunnerManager implements BaseService
{
    /**
     * @var ?Configuration
     */
    protected ?Configuration $configuration = null;
    /**
     * @var ?TaskRunnerWakeup
     */
    protected ?TaskRunnerWakeup $taskRunnerWakeupService = null;

    /**
     * Halts task runner.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function halt()
    {
        $this->getConfiguration()->setTaskRunnerHalted(true);
    }

    /**
     * Resumes task execution.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function resume()
    {
        $this->getConfiguration()->setTaskRunnerHalted(false);
        $this->getTaskRunnerWakeupService()->wakeup();
    }

    /**
     * Retrieves configuration.
     *
     * @return Configuration Configuration instance.
     */
    protected function getConfiguration(): Configuration
    {
        if ($this->configuration === null) {
            $this->configuration = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configuration;
    }

    /**
     * Retrieves task runner wakeup service.
     *
     * @return TaskRunnerWakeup Task runner wakeup instance.
     */
    protected function getTaskRunnerWakeupService(): TaskRunnerWakeup
    {
        if ($this->taskRunnerWakeupService === null) {
            $this->taskRunnerWakeupService = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        }

        return $this->taskRunnerWakeupService;
    }
}
