<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Listeners;

use DateInterval;
use Exception;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks\TransactionSynchronizer;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\QueueService;
use Unzer\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class TransactionSyncListener.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Listeners
 */
class TransactionSyncListener
{
    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     */
    public function handle(): void
    {
        if (!$this->canHandle()) {
            return;
        }

        $this->doHandle();
    }

    /**
     * @return bool
     */
    protected function canHandle(): bool
    {
        $task = $this->getQueueService()->findLatestByType(TransactionSynchronizer::getClassName());

        return !$task ||
            $task->getQueueTimestamp() < TimeProvider::getInstance()->getCurrentLocalTime()->sub(new DateInterval('P1D'))->getTimestamp();
    }

    /**
     * @return void
     *
     *
     * @throws QueueStorageUnavailableException
     * @throws Exception
     *
     */
    protected function doHandle(): void
    {
        $connectedStores = $this->getConnectionService()->getConnectedStoreIds();

        foreach ($connectedStores as $storeId) {
            $this->getQueueService()->enqueue('transaction-synchronizer-' . $storeId, new TransactionSynchronizer($storeId));
        }
    }

    /**
     * @return QueueService
     */
    protected function getQueueService(): QueueService
    {
        return ServiceRegister::getService(QueueService::class);
    }

    /**
     * Returns an instance of the connection service.
     *
     * @return ConnectionService
     */
    protected function getConnectionService(): ConnectionService
    {
        return ServiceRegister::getService(ConnectionService::class);
    }
}
