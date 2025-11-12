<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

/**
 * Interface BookingAuthorizeSupport.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums
 */
interface BookingAuthorizeSupport
{
    /** @var string[] */
    public const SUPPORTS_AUTHORIZE = [
        PaymentMethodTypes::APPLE_PAY,
        PaymentMethodTypes::CARDS,
        PaymentMethodTypes::GOOGLE_PAY,
        PaymentMethodTypes::KLARNA,
        PaymentMethodTypes::PAYPAL,
        PaymentMethodTypes::DIRECT_DEBIT_SECURED,
        PaymentMethodTypes::UNZER_INSTALLMENT,
        PaymentMethodTypes::UNZER_INVOICE,
        PaymentMethodTypes::UNZER_PAYPAGE,
        PaymentMethodTypes::WERO
    ];
}
