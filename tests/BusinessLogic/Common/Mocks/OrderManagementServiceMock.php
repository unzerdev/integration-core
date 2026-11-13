<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\ChargeResult;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\ChargeSkipReason;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\RefundResult;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\RefundSkipReason;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Services\OrderManagementService;
use UnzerSDK\Resources\Customer;

/**
 * Class OrderManagementServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class OrderManagementServiceMock extends OrderManagementService
{
    /** @var ChargeResult|null */
    private ?ChargeResult $chargeResult = null;

    /**
     * @param ChargeResult $chargeResult The result chargeOrder() returns.
     *
     * @return void
     */
    public function setChargeResult(ChargeResult $chargeResult): void
    {
        $this->chargeResult = $chargeResult;
    }

    /**
     * @param string $orderId
     * @param ?Amount $chargeAmount
     * @param string|null $reference
     *
     * @return ChargeResult
     */
    public function chargeOrder(string $orderId, ?Amount $chargeAmount, ?string $reference = null): ChargeResult
    {
        return $this->chargeResult ?? ChargeResult::skipped(ChargeSkipReason::PAYMENT_NOT_FOUND, null);
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
     * @return RefundResult
     */
    public function refundOrder(string $orderId, Amount $refundAmount, ?string $reference = null): RefundResult
    {
        return RefundResult::skipped(RefundSkipReason::NOT_NECESSARY, null);
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
