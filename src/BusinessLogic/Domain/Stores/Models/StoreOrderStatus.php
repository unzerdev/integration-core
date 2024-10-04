<?php

namespace Unzer\Core\BusinessLogic\Domain\Stores\Models;

/**
 * Class StoreOrderStatus.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Stores\Models
 */
class StoreOrderStatus
{
    /**
     * @var string
     */
    private string $statusId;

    /**
     * @var string
     */
    private string $statusName;

    /**
     * @param string $statusId
     * @param string $statusName
     */
    public function __construct(string $statusId, string $statusName)
    {
        $this->statusId = $statusId;
        $this->statusName = $statusName;
    }

    /**
     * @return string
     */
    public function getStatusId(): string
    {
        return $this->statusId;
    }

    /**
     * @return string
     */
    public function getStatusName(): string
    {
        return $this->statusName;
    }
}
