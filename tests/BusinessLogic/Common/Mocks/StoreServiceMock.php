<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
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
}
