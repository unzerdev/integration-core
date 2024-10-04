<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\Store;

use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;

/**
 * Interface StoreService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\Store
 */
interface StoreService
{
    /**
     * Returns all stores within a multiple environment.
     *
     * @return Store[]
     */
    public function getStores(): array;

    /**
     * Returns current active store.
     *
     * @return Store|null
     */
    public function getDefaultStore(): ?Store;

    /**
     * Returns current active store.
     *
     * @return Store|null
     */
    public function getCurrentStore(): ?Store;

    /**
     * Returns array of StoreOrderStatus objects.
     *
     * @return StoreOrderStatus[]
     */
    public function getStoreOrderStatuses(): array;
}
