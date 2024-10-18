<?php

namespace Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Repositories;

use Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Entities\PaymentStatusMap;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Infrastructure\ORM\QueryFilter\Operators;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class PaymentStatusMapRepository.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Repositories
 */
class PaymentStatusMapRepository implements PaymentStatusMapRepositoryInterface
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
    public function __construct(RepositoryInterface $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getPaymentStatusMap(): array
    {
        $entity = $this->getPaymentStatusMapEntity();

        return $entity ? $entity->getPaymentStatusMap() : [];
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setPaymentStatusMap(array $paymentStatusMap): void
    {
        $existingPaymentStatusMap = $this->getPaymentStatusMapEntity();

        if ($existingPaymentStatusMap) {
            $existingPaymentStatusMap->setPaymentStatusMap($paymentStatusMap);
            $existingPaymentStatusMap->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingPaymentStatusMap);

            return;
        }

        $entity = new PaymentStatusMap();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setPaymentStatusMap($paymentStatusMap);
        $this->repository->save($entity);
    }

    /**
     * @return PaymentStatusMap|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getPaymentStatusMapEntity(): ?PaymentStatusMap
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
