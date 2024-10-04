<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Stores\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;

/**
 * Class StoresResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Stores\Response
 */
class StoresResponse extends Response
{
    /**
     * @var Store[]
     */
    private array $stores;

    /**
     * @param Store[] $stores
     */
    public function __construct(array $stores)
    {
        $this->stores = $stores;
    }

    /**
     * Transforms stores to array.
     *
     * @return array Array representation of stores.
     */
    public function toArray(): array
    {
        return array_map(static function (Store $store): array {
            return (new StoreResponse($store))->toArray();
        }, $this->stores);
    }
}
