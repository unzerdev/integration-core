<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;

/**
 * Class StoreServiceMock.
 *
 * @package BusinessLogic\Common\IntegrationMocks
 */
class StoreServiceMock implements StoreService
{
    /**
     * @var Store|null
     */
    private ?Store $defaultStore = null;

    /**
     * @var Store|null
     */
    private ?Store $currentStore = null;

    /**
     * @var array
     */
    private array $stores = [];

    /**
     * @var StoreOrderStatus[]
     */
    private array $orderStatuses = [];

    /**
     * @inheritDoc
     */
    public function getStores(): array
    {
        return $this->stores;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultStore(): ?Store
    {
        return $this->defaultStore;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentStore(): ?Store
    {
        return $this->currentStore;
    }

    /**
     * @param Store $mockStore
     *
     * @return void
     */
    public function setMockDefaultStore(Store $mockStore): void
    {
        $this->defaultStore = $mockStore;
    }

    /**
     * @param ?Store $mockStore
     *
     * @return void
     */
    public function setMockCurrentStore(?Store $mockStore): void
    {
        $this->currentStore = $mockStore;
    }

    /**
     * @param Store[] $mockStores
     *
     * @return void
     */
    public function setMockStores(array $mockStores): void
    {
        $this->stores = $mockStores;
    }

    /**
     * @return StoreOrderStatus[]
     */
    public function getStoreOrderStatuses(): array
    {
        return $this->orderStatuses;
    }

    /**
     * @param array $orderStatuses
     *
     * @return void
     */
    public function setStoreOrderStatuses(array $orderStatuses): void
    {
        $this->orderStatuses = $orderStatuses;
    }

    /**
     * @param int $id
     *
     * @return Store|null
     */
    public function getStoreById(int $id): ?Store
    {
        return $this->currentStore;
    }
}
