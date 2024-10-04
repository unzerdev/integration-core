<?php

namespace Unzer\Core\BusinessLogic\Domain\Stores\Services;

use Unzer\Core\BusinessLogic\Domain\Integration\Store\StoreService as IntegrationStoreService;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;

/**
 * Class StoreService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Stores\Services
 */
class StoreService
{
    /**
     * @var IntegrationStoreService
     */
    private IntegrationStoreService $integrationStoreService;

    /**
     * @param IntegrationStoreService $integrationStoreService
     */
    public function __construct(IntegrationStoreService $integrationStoreService)
    {
        $this->integrationStoreService = $integrationStoreService;
    }

    /**
     * @return Store[]
     */
    public function getStores(): array
    {
        return $this->integrationStoreService->getStores();
    }

    /**
     * Returns first connected store. If it does not exist, default store is returned.
     *
     * @return Store|null
     */
    public function getCurrentStore(): ?Store
    {
        $currentStore = $this->integrationStoreService->getCurrentStore();

        return $currentStore ?? $this->integrationStoreService->getDefaultStore();
    }

    /**
     * @return StoreOrderStatus[]
     */
    public function getStoreOrderStatuses(): array
    {
        return $this->integrationStoreService->getStoreOrderStatuses();
    }
}
