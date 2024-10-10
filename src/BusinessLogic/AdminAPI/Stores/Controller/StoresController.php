<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Stores\Controller;

use Exception;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Response\StoreResponse;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Response\StoresResponse;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
use Unzer\Core\BusinessLogic\Domain\Stores\Services\StoreService;

/**
 * Class StoresController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Stores\Controller
 */
class StoresController
{
    /**
     * @var StoreService
     */
    private StoreService $storeService;

    /**
     * @var ConnectionService
     */
    private ConnectionService $connectionService;

    /**
     * @param StoreService $storeService
     * @param ConnectionService $connectionService
     */
    public function __construct(StoreService $storeService, ConnectionService $connectionService)
    {
        $this->storeService = $storeService;
        $this->connectionService = $connectionService;
    }

    /**
     * @return StoresResponse
     */
    public function getStores(): StoresResponse
    {
        return new StoresResponse($this->storeService->getStores());
    }

    /**
     * @return StoreResponse
     *
     * @throws Exception
     */
    public function getCurrentStore(): StoreResponse
    {
        $currentStore = $this->storeService->getCurrentStore();
        $connectionSettings = null;

        if ($currentStore !== null) {
            $connectionSettings = StoreContext::doWithStore(
                $currentStore->getStoreId(),
                [$this->connectionService, 'getConnectionSettings']
            );
        }

        $this->connectionService->getConnectionSettings();

        return $currentStore ?
            new StoreResponse($currentStore, $connectionSettings) : new StoreResponse($this->failBackStore());
    }

    /**
     * Creates failBack store in case there is no connected and default store.
     *
     * @return Store
     */
    private function failBackStore(): Store
    {
        return new Store('failBack', 'failBack');
    }
}
