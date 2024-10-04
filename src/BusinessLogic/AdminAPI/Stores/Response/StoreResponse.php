<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Stores\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;

/**
 * Class StoreResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Stores\Response
 */
class StoreResponse extends Response
{
    /**
     * @var Store
     */
    private Store $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Transforms store to array.
     *
     * @return array Array representation of store.
     */
    public function toArray(): array
    {
        return [
            'storeId' => $this->store->getStoreId(),
            'storeName' => $this->store->getStoreName()
        ];
    }
}
