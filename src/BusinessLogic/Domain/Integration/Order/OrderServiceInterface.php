<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\Order;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;

/**
 * Interface OrderServiceInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\Order
 */
interface OrderServiceInterface
{
    /**
     * @param string $orderId
     *
     * @return Amount|null
     */
    public function getRefundedAmountForOrder(string $orderId): ?Amount;

    /**
     * @param string $orderId
     * @param Amount $amount
     *
     * @return void
     */
    public function refundOrder(string $orderId, Amount $amount): void;

    /**
     * @param string $orderId
     *
     * @return Amount|null
     */
    public function getCancelledAmountForOrder(string $orderId): ?Amount;

    /**
     * @param string $orderId
     * @param Amount $amount
     * @param bool $isFullCancellation
     *
     * @return void
     */
    public function cancelOrder(string $orderId, Amount $amount, bool $isFullCancellation): void;

    /**
     * @param string $orderId
     *
     * @return Amount|null
     */
    public function getChargeAmountForOrder(string $orderId): ?Amount;

    /**
     * @param string $orderId
     * @param Amount $amount
     * @param bool $isFullCharge
     *
     * @return void
     */
    public function chargeOrder(string $orderId, Amount $amount, bool $isFullCharge): void;

    /**
     * @param string $orderId
     * @param string $statusId
     *
     * @return void
     */
    public function changeOrderStatus(string $orderId, string $statusId): void;
}
