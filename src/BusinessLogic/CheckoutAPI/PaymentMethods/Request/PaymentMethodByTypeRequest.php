<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request;

class PaymentMethodByTypeRequest
{
    private string $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}