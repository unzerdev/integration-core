<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;
use Unzer\Core\BusinessLogic\Domain\Stores\Services\StoreService;

/**
 * Class StoreServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class StoreServiceMock extends StoreService
{
    /**
     * @var Store|null
     */
    private ?Store $currentStore = null;

    /**
     * @var array
     */
    private array $stores = [];

    /**
     * @var StoreOrderStatus
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
    public function getCurrentStore(): ?Store
    {
        return $this->currentStore;
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
     * @param StoreOrderStatus[] $mockStatuses
     *
     * @return void
     */
    public function setMockStoreOrderStatuses(array $mockStatuses): void
    {
        $this->orderStatuses = $mockStatuses;
    }

    /**
     * @return array|StoreOrderStatus[]
     */
    public function getStoreOrderStatuses(): array
    {
        return $this->orderStatuses;
    }

    /**
     * @param int $storeId
     *
     * @return Store|null
     */
    public function getStoreById(int $storeId): ?Store
    {
        return $this->currentStore;
    }
}
