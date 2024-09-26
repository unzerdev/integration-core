<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use Unzer\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\QueueItem;
use Unzer\Core\Infrastructure\TaskExecution\QueueService;
use Unzer\Core\Infrastructure\TaskExecution\Task;

/**
 * Class TestQueueService.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class TestQueueService extends QueueService
{
    private array $callHistory = [];
    private array $exceptionResponses = [];

    public function getMethodCallHistory($methodName)
    {
        return !empty($this->callHistory[$methodName]) ? $this->callHistory[$methodName] : [];
    }

    public function setExceptionResponse($methodName, $exceptionToThrow)
    {
        $this->exceptionResponses[$methodName] = $exceptionToThrow;
    }

    public function requeue(QueueItem $queueItem)
    {
        if (!empty($this->exceptionResponses['requeue'])) {
            throw $this->exceptionResponses['requeue'];
        }

        $this->callHistory['requeue'][] = ['queueItem' => $queueItem];

        parent::requeue($queueItem);
    }

    public function fail(QueueItem $queueItem, $failureDescription, $force = false)
    {
        if (!empty($this->exceptionResponses['fail'])) {
            throw $this->exceptionResponses['fail'];
        }

        $this->callHistory['fail'][] = [
            'queueItem' => $queueItem, 'failureDescription' => $failureDescription, 'force' => $force
        ];

        parent::fail($queueItem, $failureDescription, $force);
    }

    public function find($id): ?QueueItem
    {
        if (!empty($this->exceptionResponses['find'])) {
            throw $this->exceptionResponses['find'];
        }

        $this->callHistory['find'][] = ['id' => $id];

        return parent::find($id);
    }

    public function start(QueueItem $queueItem)
    {
        if (!empty($this->exceptionResponses['start'])) {
            throw $this->exceptionResponses['start'];
        }

        $this->callHistory['start'][] = ['queueItem' => $queueItem];
        parent::start($queueItem);
    }

    public function finish(QueueItem $queueItem)
    {
        if (!empty($this->exceptionResponses['finish'])) {
            throw $this->exceptionResponses['finish'];
        }

        $this->callHistory['finish'][] = ['queueItem' => $queueItem];
        parent::finish($queueItem);
    }

    /**
     * Creates queue item for given task, enqueues in queue with given name and starts it
     *
     * @param $queueName
     * @param Task $task
     *
     * @param int $progress
     * @param int $lastExecutionProgress
     *
     * @return QueueItem
     * @throws QueueStorageUnavailableException
     * @throws QueueItemDeserializationException
     * @throws AbortTaskExecutionException
     */
    public function generateRunningQueueItem($queueName, Task $task, int $progress = 0, int $lastExecutionProgress = 0): QueueItem
    {
        $queueItem = $this->enqueue($queueName, $task);
        $queueItem->setProgressBasePoints($progress);
        $queueItem->setLastExecutionProgressBasePoints($lastExecutionProgress);
        $this->start($queueItem);

        return $queueItem;
    }
}
