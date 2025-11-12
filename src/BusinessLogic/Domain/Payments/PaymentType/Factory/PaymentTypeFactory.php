<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentType\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\SdkPaymentTypes;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentType\Exceptions\PaymentMethodTypeClassException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class PaymentTypeFactory
{
    /**
     * @param PaymentContext $context
     * @return BasePaymentType|null
     * @throws PaymentMethodTypeClassException
     */
    public function create(PaymentContext $context): ?BasePaymentType
    {
        $paymentType = $context->getPaymentMethodType();
        $className = array_search($paymentType, SdkPaymentTypes::PAYMENT_TYPES, true);

        if ($className === false) {
            throw new PaymentMethodTypeClassException("Class for payment type '$paymentType' not found.");
        }

        return new $className();
    }
}