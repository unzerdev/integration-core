<?php

namespace Unzer\Core\Infrastructure\TaskExecution\Events;

use Unzer\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemFailedEvent
 *
 * @package Unzer\Core\Infrastructure\TaskExecution\Events
 */
class QueueItemFailedEvent extends BaseQueueItemEvent
{
    /**
     * @var string
     */
    protected string $failureDescription;

    /**
     * QueueItemFailedEvent constructor.
     *
     * @param QueueItem $queueItem
     * @param string $failureDescription
     */
    public function __construct(QueueItem $queueItem, string $failureDescription)
    {
        parent::__construct($queueItem);
        $this->failureDescription = $failureDescription;
    }

    /**
     * @return string
     */
    public function getFailureDescription(): string
    {
        return $this->failureDescription;
    }
}
