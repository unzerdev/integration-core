<?php

namespace Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Repositories;

use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory as TransactionHistoryEntity;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Infrastructure\ORM\QueryFilter\Operators;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Unzer\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class TransactionHistoryRepository.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Repositories
 */
class TransactionHistoryRepository implements TransactionHistoryRepositoryInterface
{
    /**
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;

    /**
     * @var StoreContext
     */
    protected StoreContext $storeContext;

    /**
     * @param RepositoryInterface $repository
     * @param StoreContext $storeContext
     */
    public function __construct(
        RepositoryInterface $repository,
        StoreContext $storeContext
    ) {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setTransactionHistory(TransactionHistory $transactionHistory): void
    {
        $existingHistory = $this->getTransactionHistoryEntity($transactionHistory->getOrderId());

        if ($existingHistory) {
            $existingHistory->setTransactionHistory($transactionHistory);
            $existingHistory->setStoreId($this->storeContext->getStoreId());
            $existingHistory->setOrderId($transactionHistory->getOrderId());
            $existingHistory->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
            $this->repository->update($existingHistory);

            return;
        }

        $entity = new TransactionHistoryEntity();
        $entity->setTransactionHistory($transactionHistory);
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setOrderId($transactionHistory->getOrderId());
        $entity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getTransactionHistoryByOrderId(string $orderId): ?TransactionHistory
    {
        $entity = $this->getTransactionHistoryEntity($orderId);

        return $entity ? $entity->getTransactionHistory() : null;
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteTransactionHistoryEntities(): void
    {
        $transactionHistoryEntities = $this->getTransactionHistoryEntities();

        foreach ($transactionHistoryEntities as $transactionHistoryEntity) {
            $this->repository->delete($transactionHistoryEntity);
        }
    }

    /**
     * @param int $timeLimit
     *
     * @return array|TransactionHistory[]
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getTransactionHistoriesByUpdateTime(int $timeLimit): array
    {
        $queryFilter = (new QueryFilter())
            ->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('updatedAt', Operators::GREATER_OR_EQUAL_THAN, $timeLimit);

        /** @var TransactionHistoryEntity[] $entities */
        $entities = $this->repository->select($queryFilter);

        return array_map(fn($entity) => $entity->getTransactionHistory(), $entities);
    }

    /**
     * @return TransactionHistoryEntity[]
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getTransactionHistoryEntities(): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        return $this->repository->select($queryFilter);
    }

    /**
     * @param string $orderId
     *
     * @return ?TransactionHistoryEntity
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getTransactionHistoryEntity(string $orderId): ?TransactionHistoryEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('orderId', Operators::EQUALS, $orderId);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
