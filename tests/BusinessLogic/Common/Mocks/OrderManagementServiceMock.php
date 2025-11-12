<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Services\OrderManagementService;

/**
 * Class OrderManagementServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class OrderManagementServiceMock extends OrderManagementService
{
    /**
     * @param string $orderId
     * @param ?Amount $chargeAmount
     *
     * @return void
     */
    public function chargeOrder(string $orderId, ?Amount $chargeAmount): void
    {
    }

    /**
     * @param string $orderId
     * @param Amount|null $amount
     *
     * @return void
     */
    public function cancelOrder(string $orderId, ?Amount $amount = null): void
    {
    }

    /**
     * @param string $orderId
     * @param Amount $refundAmount
     *
     * @return void
     */
    public function refundOrder(string $orderId, Amount $refundAmount): void
    {
    }
}
