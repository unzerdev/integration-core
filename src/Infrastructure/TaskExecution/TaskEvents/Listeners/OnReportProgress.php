<?php

namespace Unzer\Core\Infrastructure\TaskExecution\TaskEvents\Listeners;

use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\Composite\Orchestrator;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\QueueItem;
use Unzer\Core\Infrastructure\TaskExecution\QueueService;
use RuntimeException;

/**
 * Class OnReportProgress
 *
 * @package Unzer\Core\Infrastructure\TaskExecution\TaskEvents\Listeners
 */
class OnReportProgress
{
    /**
     * Handles queue item progress change.
     *
     * @param QueueItem $queueItem
     * @param $progressBasePoints
     *
     * @throws QueueStorageUnavailableException
     * @throws QueueItemDeserializationException
     */
    public static function handle(QueueItem $queueItem, $progressBasePoints)
    {
        $queue = static::getQueueService();
        $queue->updateProgress($queueItem, $progressBasePoints);
        if ($queueItem->getParentId() === null) {
            return;
        }

        $parent = $queue->find($queueItem->getParentId());

        if ($parent === null) {
            throw new RuntimeException("Parent not available.");
        }

        /** @var Orchestrator $task */
        $task = $parent->getTask();
        if ($task === null || !($task instanceof Orchestrator)) {
            throw new RuntimeException("Failed to retrieve task.");
        }

        $task->updateSubJobProgress($queueItem->getId(), $queueItem->getProgressFormatted());
    }

    /**
     * Provides queue service.
     *
     * @return QueueService
     */
    private static function getQueueService(): QueueService
    {
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }
}
