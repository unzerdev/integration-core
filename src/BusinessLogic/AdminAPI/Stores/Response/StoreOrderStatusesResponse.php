<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Stores\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;

class StoreOrderStatusesResponse extends Response
{
    /**
     * @var StoreOrderStatus[]
     */
    private array $orderStatuses;

    /**
     * @param StoreOrderStatus[] $orderStatuses
     */
    public function __construct(array $orderStatuses)
    {
        $this->orderStatuses = $orderStatuses;
    }

    /**
     * Transforms order statuses to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $returnArray = [];

        foreach ($this->orderStatuses as $status) {
            $returnArray[] = [
                'id' => $status->getStatusId(),
                'name' => $status->getStatusName()
            ];
        }

        return $returnArray;
    }
}