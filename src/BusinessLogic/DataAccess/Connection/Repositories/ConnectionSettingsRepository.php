<?php

namespace Unzer\Core\BusinessLogic\DataAccess\Connection\Repositories;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Infrastructure\ORM\QueryFilter\Operators;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class ConnectionSettingsRepository.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\Connection\Repositories
 */
class ConnectionSettingsRepository implements ConnectionSettingsRepositoryInterface
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
    public function getConnectionSettings(): ?ConnectionSettings
    {
        $entity = $this->getConnectionSettingsEntity();

        return $entity ? $entity->getConnectionSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setConnectionSettings(ConnectionSettings $connectionSettings): void
    {
        $existingSettings = $this->getConnectionSettingsEntity();

        if ($existingSettings) {
            $existingSettings->setConnectionSettings($connectionSettings);
            $existingSettings->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingSettings);

            return;
        }

        $entity = new ConnectionSettingsEntity();
        $entity->setConnectionSettings($connectionSettings);
        $entity->setStoreId($this->storeContext->getStoreId());
        $this->repository->save($entity);
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteConnectionSettings(): void
    {
        $connectionSettingsEntity = $this->getConnectionSettingsEntity();

        if (!$connectionSettingsEntity) {
            return;
        }

        $this->repository->delete($connectionSettingsEntity);
    }

    /**
     * @return string[]
     */
    public function getAllConnectedStoreIds(): array
    {
        $ids = [];

        /** @var ConnectionSettingsEntity[] $connectionEntities */
        $connectionEntities = $this->repository->select();

        foreach ($connectionEntities as $connectionSettings) {
            $ids[] = $connectionSettings->getStoreId();
        }

        return $ids;
    }

    /**
     * @return ConnectionSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getConnectionSettingsEntity(): ?ConnectionSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
