<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\Common\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\DataBag;

abstract class PaymentContext
{
    private string $paymentMethodType;
    private string $orderId;
    private Amount $amount;
    private string $returnUrl;
    private DataBag $checkoutSession;
    private string $locale;

    /**
     * PaymentPageCreateContext constructor.
     * @param string $paymentMethodType
     * @param string $orderId
     * @param Amount $amount
     * @param string $returnUrl
     * @param DataBag $checkoutSession
     * @param string $locale
     */
    public function __construct(
        string $paymentMethodType,
        string $orderId,
        Amount $amount,
        string $returnUrl,
        DataBag $checkoutSession,
        string $locale = 'default'
    ) {
        $this->paymentMethodType = $paymentMethodType;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
        $this->checkoutSession = $checkoutSession;
        $this->locale = $locale;
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

    public function getSessionData(): DataBag
    {
        return $this->checkoutSession;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}