<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use Unzer\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use Unzer\Core\Infrastructure\TaskExecution\Task;

/**
 * Class AbortTask.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class AbortTask extends Task
{
    public function execute()
    {
        throw new AbortTaskExecutionException('Abort mission!');
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array): \Unzer\Core\Infrastructure\Serializer\Interfaces\Serializable
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array();
    }
}
