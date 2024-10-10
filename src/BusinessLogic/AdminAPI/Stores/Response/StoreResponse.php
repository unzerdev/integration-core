<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Stores\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
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
     * @var ConnectionSettings
     */
    private ?ConnectionSettings $connectionSettings;

    /**
     * @param Store $store
     * @param ConnectionSettings|null $connectionSettings
     */
    public function __construct(Store $store, ?ConnectionSettings $connectionSettings = null)
    {
        $this->store = $store;
        $this->connectionSettings = $connectionSettings;
    }

    /**
     * Transforms store to array.
     *
     * @return array Array representation of store.
     */
    public function toArray(): array
    {
        $returnArray = [
            'storeId' => $this->store->getStoreId(),
            'storeName' => $this->store->getStoreName(),
            'isLoggedIn' => false,
            'mode' => Mode::live()->getMode(),
            'publicKey' => ''
        ];

        if($this->connectionSettings !== null) {
            $returnArray['mode'] = $this->connectionSettings->getMode()->getMode();
            $returnArray['publicKey'] = $this->connectionSettings->getActiveConnectionData()->getPublicKey();
            $returnArray['isLoggedIn'] = true;
        }

        return $returnArray;
    }
}
