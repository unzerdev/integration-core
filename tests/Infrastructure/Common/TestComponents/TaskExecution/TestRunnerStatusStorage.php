<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use Unzer\Core\Infrastructure\TaskExecution\TaskRunnerStatus;

/**
 * Class TestRunnerStatusStorage.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class TestRunnerStatusStorage implements TaskRunnerStatusStorage
{
    /** @var TaskRunnerStatus|null */
    private ?TaskRunnerStatus $status = null;
    private array $callHistory = [];
    private array $exceptionResponses = [];

    public function getMethodCallHistory($methodName)
    {
        return !empty($this->callHistory[$methodName]) ? $this->callHistory[$methodName] : [];
    }

    public function initializeStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus(): TaskRunnerStatus
    {
        if (!empty($this->exceptionResponses['getStatus'])) {
            throw $this->exceptionResponses['getStatus'];
        }

        $this->callHistory['getStatus'][] = [];

        return $this->status !== null ? $this->status : TaskRunnerStatus::createNullStatus();
    }

    public function setStatus(TaskRunnerStatus $status)
    {
        if (!empty($this->exceptionResponses['setStatus'])) {
            throw $this->exceptionResponses['setStatus'];
        }

        $this->callHistory['setStatus'][] = ['status' => $status];
        $this->status = $status;
    }

    public function setExceptionResponse($methodName, $exceptionToThrow)
    {
        $this->exceptionResponses[$methodName] = $exceptionToThrow;
    }
}
