<?php

namespace Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Repositories;

use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig as PaymentMethodConfigEntity;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Infrastructure\ORM\QueryFilter\Operators;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class PaymentMethodConfigRepository.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Repositories
 */
class PaymentMethodConfigRepository implements PaymentMethodConfigRepositoryInterface
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
    public function getPaymentMethodConfigs(): array
    {
        $entities = $this->getPaymentConfigEntities();
        $paymentMethods = [];

        foreach ($entities as $entity) {
            $paymentMethods[] = $entity->getPaymentMethodConfig();
        }

        return $paymentMethods;
    }

    /**
     * @param PaymentMethodConfig $paymentMethodConfig
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function savePaymentMethodConfig(PaymentMethodConfig $paymentMethodConfig): void
    {
        $existingConfig = $this->getPaymentConfigEntity($paymentMethodConfig->getType());

        if ($existingConfig) {
            $existingConfig->setPaymentMethodConfig($paymentMethodConfig);
            $existingConfig->setStoreId($this->storeContext->getStoreId());
            $existingConfig->setType($paymentMethodConfig->getType());
            $this->repository->update($existingConfig);

            return;
        }

        $entity = new PaymentMethodConfigEntity();
        $entity->setPaymentMethodConfig($paymentMethodConfig);
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setType($paymentMethodConfig->getType());
        $this->repository->save($entity);
    }

    /**
     * @param PaymentMethodConfig $paymentMethodConfig
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function enablePaymentMethodConfig(PaymentMethodConfig $paymentMethodConfig): void
    {
        $existingConfig = $this->getPaymentConfigEntity($paymentMethodConfig->getType());

        if ($existingConfig) {
            $existingPaymentMethodConfig = $existingConfig->getPaymentMethodConfig();
            $existingPaymentMethodConfig->setEnabled($paymentMethodConfig->isEnabled());
            $existingConfig->setPaymentMethodConfig($existingPaymentMethodConfig);
            $existingConfig->setStoreId($this->storeContext->getStoreId());
            $existingConfig->setType($paymentMethodConfig->getType());
            $this->repository->update($existingConfig);

            return;
        }

        $entity = new PaymentMethodConfigEntity();
        $entity->setPaymentMethodConfig($paymentMethodConfig);
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setType($paymentMethodConfig->getType());
        $this->repository->save($entity);
    }

    /**
     * @param string $type
     *
     * @return ?PaymentMethodConfig
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getPaymentMethodConfigByType(string $type): ?PaymentMethodConfig
    {
        $entity = $this->getPaymentConfigEntity($type);

        return $entity ? $entity->getPaymentMethodConfig() : null;
    }

    /**
     * @return PaymentMethodConfigEntity[]
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getPaymentConfigEntities(): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        return $this->repository->select($queryFilter);
    }

    /**
     * @param string $type
     *
     * @return PaymentMethodConfigEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getPaymentConfigEntity(string $type): ?PaymentMethodConfigEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('type', Operators::EQUALS, $type);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
