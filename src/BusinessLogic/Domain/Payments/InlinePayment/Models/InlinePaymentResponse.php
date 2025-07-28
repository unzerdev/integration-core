<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models;

class InlinePaymentResponse
{
    protected string $returnUrl;

    public function __construct(string $returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}