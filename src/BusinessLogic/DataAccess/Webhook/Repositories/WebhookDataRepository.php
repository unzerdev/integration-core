<?php

namespace Unzer\Core\BusinessLogic\DataAccess\Webhook\Repositories;

use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookData as WebhookDataEntity;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Infrastructure\ORM\QueryFilter\Operators;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class WebhookDataRepository.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\Webkhook\Repositories
 */
class WebhookDataRepository implements WebhookDataRepositoryInterface
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
    public function getWebhookData(): ?WebhookData
    {
        $entity = $this->getWebhookDataEntity();

        return $entity ? $entity->getWebhookData() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setWebhookData(WebhookData $webhookData): void
    {
        $existingData = $this->getWebhookDataEntity();

        if ($existingData) {
            $existingData->setWebhookData($webhookData);
            $existingData->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingData);

            return;
        }

        $entity = new WebhookDataEntity();
        $entity->setWebhookData($webhookData);
        $entity->setStoreId($this->storeContext->getStoreId());
        $this->repository->save($entity);
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteWebhookData(): void
    {
        $webhookDataEntity = $this->getWebhookDataEntity();

        if (!$webhookDataEntity) {
            return;
        }

        $this->repository->delete($webhookDataEntity);
    }

    /**
     * @return WebhookDataEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getWebhookDataEntity(): ?WebhookDataEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
