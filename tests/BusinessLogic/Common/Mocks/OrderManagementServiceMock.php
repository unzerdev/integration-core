<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Services\OrderManagementService;
use UnzerSDK\Resources\Customer;

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
     * @param string|null $reference
     *
     * @return void
     */
    public function chargeOrder(string $orderId, ?Amount $chargeAmount, ?string $reference = null): void
    {
    }

    /**
     * @param string $orderId
     * @param Amount|null $amount
     * @param string|null $reference
     *
     * @return void
     */
    public function cancelOrder(string $orderId, ?Amount $amount = null, ?string $reference = null): void
    {
    }

    /**
     * @param string $orderId
     * @param Amount $refundAmount
     * @param string|null $reference
     *
     * @return void
     */
    public function refundOrder(string $orderId, Amount $refundAmount, ?string $reference = null): void
    {
    }

    /**
     * @param string $orderId
     * @param Customer $customer
     *
     * @return void
     */
    public function updateCustomer(string $orderId, Customer $customer): void
    {
    }
}
