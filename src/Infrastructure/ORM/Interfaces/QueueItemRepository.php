<?php

namespace Unzer\Core\Infrastructure\ORM\Interfaces;

use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use Unzer\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Interface QueueRepository.
 *
 * @package Unzer\Core\Infrastructure\ORM\Interfaces
 * @method QueueItem[] select(QueryFilter $filter = null)
 * @method QueueItem|null selectOne(QueryFilter $filter = null)
 */
interface QueueItemRepository extends RepositoryInterface
{
    /**
     * Finds list of earliest queued queue items per queue for given priority.
     * Following list of criteria for searching must be satisfied:
     *      - Queue must be without already running queue items
     *      - For one queue only one (oldest queued) item should be returned
     *      - Only queue items with given priority can be retrieved.
     *
     * @param int $priority Queue item priority priority.
     * @param int $limit Result set limit. By default max 10 earliest queue items will be returned
     *
     * @return QueueItem[] Found queue item list
     */
    public function findOldestQueuedItems(int $priority, int $limit = 10): array;

    /**
     * Creates or updates given queue item. If queue item id is not set, new queue item will be created otherwise
     * update will be performed.
     *
     * @param QueueItem $queueItem Item to save
     * @param array $additionalWhere List of key/value pairs that must be satisfied upon saving queue item. Key is
     *  queue item property and value is condition value for that property. Example for MySql storage:
     *  $storage->save($queueItem, array('status' => 'queued')) should produce query
     *  UPDATE queue_storage_table SET .... WHERE .... AND status => 'queued'
     *
     * @return int Id of saved queue item
     * @throws QueueItemSaveException if queue item could not be saved
     */
    public function saveWithCondition(QueueItem $queueItem, array $additionalWhere = []): int;

    /**
     * Updates status of a batch of queue items.
     *
     * @param array $ids
     * @param string $status
     *
     * @return void
     */
    public function batchStatusUpdate(array $ids, string $status);
}
