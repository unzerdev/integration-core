<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use Unzer\Core\Infrastructure\TaskExecution\Composite\ExecutionDetails;
use Unzer\Core\Infrastructure\TaskExecution\Composite\Orchestrator;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

class FooOrchestrator extends Orchestrator
{
    const SUB_JOB_COUNT = 3;
    /**
     * List of subtasks created and managed by the orchestrator
     *
     * @var ExecutionDetails[]
     */
    public array $taskList = [];

    /**
     * @return ExecutionDetails|null
     *
     * @throws QueueStorageUnavailableException
     */
    protected function getSubTask(): ?ExecutionDetails
    {
        if (count($this->taskList) === self::SUB_JOB_COUNT) {
            return null;
        }

        return $this->createSubJob(new FooTask());
    }
}
