<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;

/**
 * Class TransactionHistory.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models
 */
class TransactionHistory
{
    /**
     * Payment type used for transaction.
     *
     * @var string $type
     */
    private string $type;

    /**
     * Unzer payment ID.
     *
     * @var string $paymentId
     */
    private string $paymentId;

    /**
     * Shop order ID.
     *
     * @var string $orderId
     */
    private string $orderId;

    /**
     * @var ?PaymentState $paymentState
     */
    private ?PaymentState $paymentState;

    /**
     * @var ?Amount $totalAmount
     */
    private ?Amount $totalAmount;

    /**
     * @var ?Amount $chargedAmount
     */
    private ?Amount $chargedAmount;

    /**
     * Cancelled/refunded amount
     *
     * @var ?Amount $cancelledAmount
     */
    private ?Amount $cancelledAmount;

    /**
     * @var ?Amount $remainingAmount
     */
    private ?Amount $remainingAmount;

    /**
     * @param string $type
     * @param string $paymentId
     * @param string $orderId
     * @param PaymentState|null $paymentState
     * @param Amount|null $totalAmount
     * @param Amount|null $chargedAmount
     * @param Amount|null $cancelledAmount
     * @param Amount|null $remainingAmount
     */
    public function __construct(
        string $type,
        string $paymentId,
        string $orderId,
        ?PaymentState $paymentState = null,
        ?Amount $totalAmount = null,
        ?Amount $chargedAmount = null,
        ?Amount $cancelledAmount = null,
        ?Amount $remainingAmount = null
    ) {
        $this->type = $type;
        $this->paymentId = $paymentId;
        $this->orderId = $orderId;
        $this->paymentState = $paymentState;
        $this->totalAmount = $totalAmount;
        $this->chargedAmount = $chargedAmount;
        $this->cancelledAmount = $cancelledAmount;
        $this->remainingAmount = $remainingAmount;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * @param string $paymentId
     *
     * @return void
     */
    public function setPaymentId(string $paymentId): void
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     *
     * @return void
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return PaymentState|null
     */
    public function getPaymentState(): ?PaymentState
    {
        return $this->paymentState;
    }

    /**
     * @param PaymentState|null $paymentState
     *
     * @return void
     */
    public function setPaymentState(?PaymentState $paymentState): void
    {
        $this->paymentState = $paymentState;
    }

    /**
     * @return Amount|null
     */
    public function getTotalAmount(): ?Amount
    {
        return $this->totalAmount;
    }

    /**
     * @param Amount|null $totalAmount
     *
     * @return void
     */
    public function setTotalAmount(?Amount $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    /**
     * @return Amount|null
     */
    public function getChargedAmount(): ?Amount
    {
        return $this->chargedAmount;
    }

    /**
     * @param Amount|null $chargedAmount
     *
     * @return void
     */
    public function setChargedAmount(?Amount $chargedAmount): void
    {
        $this->chargedAmount = $chargedAmount;
    }

    /**
     * @return Amount|null
     */
    public function getCancelledAmount(): ?Amount
    {
        return $this->cancelledAmount;
    }

    /**
     * @param Amount|null $cancelledAmount
     *
     * @return void
     */
    public function setCancelledAmount(?Amount $cancelledAmount): void
    {
        $this->cancelledAmount = $cancelledAmount;
    }

    /**
     * @return Amount|null
     */
    public function getRemainingAmount(): ?Amount
    {
        return $this->remainingAmount;
    }

    /**
     * @param Amount|null $remainingAmount
     *
     * @return void
     */
    public function setRemainingAmount(?Amount $remainingAmount): void
    {
        $this->remainingAmount = $remainingAmount;
    }
}
