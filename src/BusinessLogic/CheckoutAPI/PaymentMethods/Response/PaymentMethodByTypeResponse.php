<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Response;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;

class PaymentMethodByTypeResponse
{
    private ?PaymentMethodConfig $paymentMethodConfig;

    /**
     * @param PaymentMethodConfig|null $paymentMethodConfig
     */
    public function __construct(?PaymentMethodConfig $paymentMethodConfig)
    {
        $this->paymentMethodConfig = $paymentMethodConfig;
    }

    /**
     * @return PaymentMethodConfig|null
     */
    public function getPaymentMethodConfig(): ?PaymentMethodConfig
    {
        return $this->paymentMethodConfig;
    }
}