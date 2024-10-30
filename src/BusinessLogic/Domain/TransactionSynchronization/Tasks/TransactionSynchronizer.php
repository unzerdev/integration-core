<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\Infrastructure\Serializer\Serializer;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\Composite\ExecutionDetails;
use Unzer\Core\Infrastructure\TaskExecution\Composite\Orchestrator;
use Unzer\Core\Infrastructure\Serializer\Interfaces\Serializable;

/**
 * Class TransactionSynchronizer.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks
 */
class TransactionSynchronizer extends Orchestrator
{
    /** @var int */
    private const TRANSACTIONS_COUNT_TO_SYNC = 5;

    /**
     * @var string[] $orderIds
     */
    private array $orderIds = [];


    /** @var string $storeId */
    private string $storeId;

    /**
     * @throws Exception
     */
    public function __construct(string $storeId)
    {
        $this->storeId = $storeId;
        $this->orderIds = $this->getOrderIdsToSynchronize();
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
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function getSubTask(): ?ExecutionDetails
    {
        $orderIdsToSynchronize = array_splice($this->orderIds, 0, self::TRANSACTIONS_COUNT_TO_SYNC);

        if (empty($orderIdsToSynchronize)) {
            return null;
        }

        return $this->createSubJob(new TransactionSyncTask($orderIdsToSynchronize));
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
     * @return TransactionHistory[]
     *
     * @throws Exception
     */
    private function getOrderIdsToSynchronize(): array
    {
        return StoreContext::doWithStore(
            $this->storeId,
            [$this->getTransactionHistoryService(), 'getOrderIdsForSynchronization']
        );
    }
}
