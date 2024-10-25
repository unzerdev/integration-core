<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\DataBag;

/**
 * Class PaymentPageCreateContext
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Models
 */
class PaymentPageCreateContext
{
    private string $paymentMethodType;
    private string $orderId;
    private Amount $amount;
    private string $returnUrl;
    private DataBag $checkoutSession;

    /**
     * PaymentPageCreateContext constructor.
     * @param string $paymentMethodType
     * @param string $orderId
     * @param Amount $amount
     * @param string $returnUrl
     * @param DataBag $checkoutSession
     */
    public function __construct(
        string $paymentMethodType,
        string $orderId,
        Amount $amount,
        string $returnUrl,
        DataBag $checkoutSession
    ) {
        $this->paymentMethodType = $paymentMethodType;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
        $this->checkoutSession = $checkoutSession;
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

    public function getCheckoutSession(): DataBag
    {
        return $this->checkoutSession;
    }
}
