<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Unzer\Core\Infrastructure\TaskExecution\Task;

/**
 * Class InvalidTask.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class InvalidTask extends Task
{
    public function execute()
    {
    }

    /**
     * @inheritdoc
     * @throws QueueItemDeserializationException
     */
    public function unserialize($serialized)
    {
        throw new QueueItemDeserializationException("Failed to deserialize task.");
    }

    /**
     * @inheritDoc
     * @throws QueueItemDeserializationException
     */
    public static function fromArray(array $array): \Unzer\Core\Infrastructure\Serializer\Interfaces\Serializable
    {
        throw new QueueItemDeserializationException("Failed to deserialize task.");
    }
}
