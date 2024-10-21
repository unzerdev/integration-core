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
            $this->repository->update($existingHistory);

            return;
        }

        $entity = new TransactionHistoryEntity();
        $entity->setTransactionHistory($transactionHistory);
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setOrderId($transactionHistory->getOrderId());
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
