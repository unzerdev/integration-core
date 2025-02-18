<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
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

    /** @var array */
    private array $callHistory = [];

    /**
     * @inheritDoc
     */
    public function getRefundedAmountForOrder(string $orderId): Amount
    {
        if (!self::$refundedAmount) {
            self::$refundedAmount = $this->defaultAmount();
        }

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
        $this->callHistory['refundOrder'][] = ['orderId' => $orderId, 'amount' => $amount->getPriceInCurrencyUnits()];
    }

    /**
     * @inheritDoc
     */
    public function getCancelledAmountForOrder(string $orderId): Amount
    {
        if (!self::$cancelledAmount) {
            self::$cancelledAmount = $this->defaultAmount();
        }

        return self::$cancelledAmount;
    }

    /**
     * @param string $orderId
     * @param Amount $amount
     * @param bool $isFullCancellation *
     *
     * @inheritDoc
     */
    public function cancelOrder(string $orderId, Amount $amount, bool $isFullCancellation): void
    {
        $this->callHistory['cancelOrder'][] = ['orderId' => $orderId, 'amount' => $amount->getPriceInCurrencyUnits()];
    }

    /**
     * @inheritDoc
     */
    public function getChargeAmountForOrder(string $orderId): Amount
    {
        if (!self::$chargedAmount) {
            self::$chargedAmount = $this->defaultAmount();
        }

        return self::$chargedAmount;
    }

    /**
     * @param string $orderId
     * @param Amount $amount
     * @param bool $isFullCharge *
     *
     * @inheritDoc
     */
    public function chargeOrder(string $orderId, Amount $amount, bool $isFullCharge): void
    {
        $this->callHistory['chargeOrder'][] = ['orderId' => $orderId, 'amount' => $amount->getPriceInCurrencyUnits()];
    }

    /**
     * @inheritDoc
     */
    public function changeOrderStatus(string $orderId, string $statusId): void
    {
        $this->callHistory['changeOrderStatus'][] = ['orderId' => $orderId, 'statusId' => $statusId];
    }

    /**
     * @param string $methodName
     *
     * @return array
     */
    public function getCallHistory(string $methodName): array
    {
        return !empty($this->callHistory[$methodName]) ? $this->callHistory[$methodName] : [];
    }

    /**
     * @return Amount
     */
    private function defaultAmount(): Amount
    {
        return Amount::fromInt(0, Currency::getDefault());
    }
}
