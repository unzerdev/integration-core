<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

/**
 * Interface UnsupportedPaymentTypes.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums
 */
interface UnsupportedPaymentTypes
{
    /** @var string[] */
    public const UNSUPPORTED_METHOD_TYPES = [
        PaymentMethodTypes::SOFORT,
        PaymentMethodTypes::GIROPAY,
        'installment-secured',
        'sepa-direct-debit-secured',
        'PIS',
        'invoice-secured',
        // Click to pay is merged with credit card payment method. This insures that there will be 1 configuration
        PaymentMethodTypes::CLICK_TO_PAY
    ];
}
