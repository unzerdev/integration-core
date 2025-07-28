<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class InlinePaymentRequest
{
    protected Amount $amount;
    protected string $returnUrl;
    protected BasePaymentType $paymentType;

    public function __construct(Amount $amount, string $returnUrl, BasePaymentType $paymentType)
    {
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
        $this->paymentType = $paymentType;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    public function getPaymentType(): BasePaymentType
    {
        return $this->paymentType;
    }
}