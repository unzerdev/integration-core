<?php

namespace Unzer\Core\Infrastructure\TaskExecution\Events;

use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Unzer\Core\Infrastructure\TaskExecution\Task;

/**
 * Class QueueItemEnqueuedEvent
 *
 * @package Unzer\Core\Infrastructure\TaskExecution\Events
 */
class QueueItemEnqueuedEvent extends BaseQueueItemEvent
{
    /**
     * @var string
     */
    protected string $queueName;

    /**
     * @var Task
     */
    protected Task $task;

    /**
     * @var string
     */
    protected string $context;

    /**
     * @var int
     */
    protected int $priority;

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->getQueueItem()->getQueueName();
    }

    /**
     * @return Task
     *
     * @throws QueueItemDeserializationException
     */
    public function getTask(): Task
    {
        return $this->getQueueItem()->getTask();
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->getQueueItem()->getContext();
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->getQueueItem()->getPriority();
    }
}
