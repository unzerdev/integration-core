<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentType\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\SdkPaymentTypes;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Processors\BasketProcessorsRegistry;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class PaymentTypeFactory
{
    public function create(PaymentContext $context): ?BasePaymentType
    {
        $paymentType = $context->getPaymentMethodType();
        $className = array_search($paymentType, SdkPaymentTypes::PAYMENT_TYPES, true);

        if ($className === false) {
            throw new \RuntimeException("Class for payment type '$paymentType' not found.");
        }

        return new $className($paymentType);
    }
}