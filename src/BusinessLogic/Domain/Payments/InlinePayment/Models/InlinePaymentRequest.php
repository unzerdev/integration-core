<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class InlinePaymentRequest
{
    protected Amount $amount;
    protected string $returnUrl;
    protected BasePaymentType $paymentType;

    /**
     * @param Amount $amount
     * @param string $returnUrl
     * @param BasePaymentType $paymentType
     */
    public function __construct(Amount $amount, string $returnUrl, BasePaymentType $paymentType)
    {
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
        $this->paymentType = $paymentType;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * @return BasePaymentType
     */
    public function getPaymentType(): BasePaymentType
    {
        return $this->paymentType;
    }

    /**
     * @param Amount $amount
     *
     * @return void
     */
    public function setAmount(Amount $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @param string $returnUrl
     *
     * @return void
     */
    public function setReturnUrl(string $returnUrl): void
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @param BasePaymentType $paymentType
     *
     * @return void
     */
    public function setPaymentType(BasePaymentType $paymentType): void
    {
        $this->paymentType = $paymentType;
    }
}
