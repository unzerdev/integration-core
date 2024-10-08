<?php

namespace Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Repositories;

use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings as PaymentPageSettingsEntity;
use Unzer\Core\Infrastructure\ORM\QueryFilter\Operators;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class PaymentPageSettingsRepository
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Repositories
 */
class PaymentPageSettingsRepository implements PaymentPageSettingsRepositoryInterface
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
     * @return PaymentPageSettings|null
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getPaymentPageSettings(): ?PaymentPageSettings
    {
        $entity = $this->getPaymentPageSettingsEntity();

        return $entity ? $entity->getPaymentPageSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @param PaymentPageSettings $paymentPageSettings
     *
     * @return void
     * @throws QueryFilterInvalidParamException
     */

    public function setPaymentPageSettings(PaymentPageSettings $paymentPageSettings): void
    {
        $existingSettings = $this->getPaymentPageSettingsEntity();

        if ($existingSettings) {
            $existingSettings->setPaymentPageSetting($paymentPageSettings);
            $existingSettings->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingSettings);

            return;
        }

        $entity = new PaymentPageSettingsEntity();
        $entity->setPaymentPageSetting($paymentPageSettings);
        $entity->setStoreId($this->storeContext->getStoreId());
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     * @throws QueryFilterInvalidParamException
     */
    public function deletePaymentPageSettings(): void
    {
        $settings = $this->getPaymentPageSettingsEntity();

        if (!$settings) {
            return;
        }

        $this->repository->delete($settings);
    }

    /**
     * @return PaymentPageSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getPaymentPageSettingsEntity(): ?PaymentPageSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}