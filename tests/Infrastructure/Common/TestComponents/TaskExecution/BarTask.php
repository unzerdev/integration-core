<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

/**
 * Class BarTask.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class BarTask extends FooTask
{
    public function execute()
    {
        parent::execute();

        $this->reportProgress(100);
    }
}
