<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Integration\Order\OrderServiceInterface;

/**
 * Class OrderServiceMock.
 *
 * @package BusinessLogic\Common\IntegrationMocks
 */
class OrderServiceMock implements OrderServiceInterface
{
    /** @var Amount|null */
    private static ?Amount $refundedAmount = null;

    /** @var Amount|null */
    private static ?Amount $cancelledAmount = null;

    /** @var Amount|null */
    private static ?Amount $chargedAmount = null;

    /**
     * @inheritDoc
     */
    public function getRefundedAmountForOrder(string $orderId): ?Amount
    {
        return self::$refundedAmount;
    }

    /**
     * @param Amount $refundedAmount
     *
     * @return void
     */
    public function setRefundedAmount(Amount $refundedAmount): void
    {
        self::$refundedAmount = $refundedAmount;
    }

    /**
     * @param Amount $cancelled
     *
     * @return void
     */
    public function setCancelledAmount(Amount $cancelled): void
    {
        self::$cancelledAmount = $cancelled;
    }

    /**
     * @param Amount $chargedAmount
     *
     * @return void
     */
    public function setChargedAmount(Amount $chargedAmount): void
    {
        self::$chargedAmount = $chargedAmount;
    }

    /**
     * @inheritDoc
     */
    public function refundOrder(string $orderId, Amount $amount): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getCancelledAmountForOrder(string $orderId): ?Amount
    {
       return self::$cancelledAmount;
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder(string $orderId, Amount $amount): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getChargeAmountForOrder(string $orderId): ?Amount
    {
        return self::$chargedAmount;
    }

    /**
     * @inheritDoc
     */
    public function chargeOrder(string $orderId, Amount $amount): void
    {
    }

    /**
     * @inheritDoc
     */
    public function changeOrderStatus(string $orderId, string $statusId): void
    {
    }
}
