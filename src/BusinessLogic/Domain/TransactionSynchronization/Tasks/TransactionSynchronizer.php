<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\Infrastructure\Serializer\Serializer;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\Serializer\Interfaces\Serializable;
use Unzer\Core\Infrastructure\TaskExecution\QueueService;
use Unzer\Core\Infrastructure\TaskExecution\Task;

/**
 * Class TransactionSynchronizer.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks
 */
class TransactionSynchronizer extends Task
{
    /** @var int */
    private const TRANSACTIONS_COUNT_TO_SYNC = 100;

    /** @var string $storeId */
    private string $storeId;

    /**
     * @throws Exception
     */
    public function __construct(string $storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $result = parent::toArray();
        $result['storeId'] = $this->storeId;

        return $result;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public static function fromArray(array $array): Serializable
    {
        return new static($array['storeId']);
    }

    /**
     * @inheritDoc
     */
    public function serialize(): string
    {
        return Serializer::serialize([
            'parent' => parent::serialize(),
            'storeId' => $this->storeId,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized): void
    {
        $unserialized = Serializer::unserialize($serialized);
        parent::unserialize($unserialized['parent']);
        $this->storeId = $unserialized['storeId'];
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function execute(): void
    {
        StoreContext::doWithStore($this->storeId, function () {
            $paymentIDs = $this->getTransactionHistoryService()->getPaymentIdsForSynchronization();

            $totalOrders = count($paymentIDs);
            $offset = 0;

            while ($offset < $totalOrders) {
                $paymentIdsToSynchronize = array_slice($paymentIDs, $offset, self::TRANSACTIONS_COUNT_TO_SYNC);

                if (empty($paymentIdsToSynchronize)) {
                    break;
                }

                $this->getQueueService()->enqueue(
                    'transaction-sync-' . $this->storeId . '-' . ($offset % 3),
                    new TransactionSyncTask($paymentIdsToSynchronize)
                );

                $offset += self::TRANSACTIONS_COUNT_TO_SYNC;
            }
        });

        $this->reportProgress(100);
    }

    /**
     * Returns an instance of the transaction history service.
     *
     * @return TransactionHistoryService
     */
    protected function getTransactionHistoryService(): TransactionHistoryService
    {
        return ServiceRegister::getService(TransactionHistoryService::class);
    }

    /**
     * @return QueueService
     */
    protected function getQueueService(): QueueService
    {
        return ServiceRegister::getService(QueueService::class);
    }
}
