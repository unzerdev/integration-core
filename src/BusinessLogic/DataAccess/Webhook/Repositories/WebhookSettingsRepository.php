<?php

namespace Unzer\Core\BusinessLogic\DataAccess\Webhook\Repositories;

use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookSettings as WebhookSettingsEntity;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Infrastructure\ORM\QueryFilter\Operators;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class WebhookDataRepository.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\Webkhook\Repositories
 */
class WebhookSettingsRepository implements WebhookSettingsRepositoryInterface
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
    public function getWebhookSettings(): ?WebhookSettings
    {
        $entity = $this->getWebhookSettingsEntity();

        return $entity ? $entity->getWebhookSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setWebhookSettings(WebhookSettings $webhookData): void
    {
        $existingData = $this->getWebhookSettingsEntity();

        if ($existingData) {
            $existingData->setWebhookSettings($webhookData);
            $existingData->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingData);

            return;
        }

        $entity = new WebhookSettingsEntity();
        $entity->setWebhookSettings($webhookData);
        $entity->setStoreId($this->storeContext->getStoreId());
        $this->repository->save($entity);
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteWebhookSettings(): void
    {
        $webhookDataEntity = $this->getWebhookSettingsEntity();

        if (!$webhookDataEntity) {
            return;
        }

        $this->repository->delete($webhookDataEntity);
    }

    /**
     * @return WebhookSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getWebhookSettingsEntity(): ?WebhookSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
