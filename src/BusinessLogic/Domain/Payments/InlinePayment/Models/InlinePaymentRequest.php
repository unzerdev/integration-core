<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;

class InlinePaymentRequest
{
    protected Amount $amount;
    protected string $returnUrl;
    protected string $paymentTypeId;

    public function __construct(Amount $amount, string $returnUrl, string $paymentTypeId)
    {
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
        $this->paymentTypeId = $paymentTypeId;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    public function getPaymentTypeId(): string
    {
        return $this->paymentTypeId;
    }
}