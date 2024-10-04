<?php

namespace Unzer\Core\BusinessLogic\Domain\Stores\Models;

/**
 * Class Store.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Stores\Models
 */
class Store
{
    /**
     * @var string
     */
    private string $storeId;

    /**
     * @var string
     */
    private string $storeName;

    /**
     * @param string $storeId
     * @param string $storeName
     */
    public function __construct(string $storeId, string $storeName)
    {
        $this->storeId = $storeId;
        $this->storeName = $storeName;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @return string
     */
    public function getStoreName(): string
    {
        return $this->storeName;
    }
}
