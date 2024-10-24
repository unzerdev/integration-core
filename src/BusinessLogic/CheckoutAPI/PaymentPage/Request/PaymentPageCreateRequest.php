<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;

/**
 * Class PaymentPageCreateRequest
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request
 */
class PaymentPageCreateRequest
{
    private string $paymentMethodType;
    private string $orderId;
    private Amount $amount;
    private string $returnUrl;

    /**
     * PaymentPageCreateRequest constructor.
     * @param string $paymentMethodType
     * @param string $orderId
     * @param Amount $amount
     * @param string $returnUrl
     */
    public function __construct(string $paymentMethodType, string $orderId, Amount $amount, string $returnUrl)
    {
        $this->paymentMethodType = $paymentMethodType;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
    }

    public function getPaymentMethodType(): string
    {
        return $this->paymentMethodType;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}
