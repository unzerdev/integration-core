<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use Unzer\Core\Infrastructure\TaskExecution\TaskRunner;

/**
 * Class TestTaskRunner.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class TestTaskRunner extends TaskRunner
{
    private array $callHistory = [];

    public function getMethodCallHistory($methodName)
    {
        return !empty($this->callHistory[$methodName]) ? $this->callHistory[$methodName] : [];
    }

    public function run()
    {
        $this->callHistory['run'][] = [];
    }

    public function setGuid($guid): void
    {
        $this->callHistory['setGuid'][] = ['guid' => $guid];
        parent::setGuid($guid);
    }
}
