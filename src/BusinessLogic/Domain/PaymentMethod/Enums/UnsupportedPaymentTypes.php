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
        PaymentMethodTypes::APPLE_PAY
    ];
}
