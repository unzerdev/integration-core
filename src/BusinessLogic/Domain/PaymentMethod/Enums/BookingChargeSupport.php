<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

/**
 * Interface BookingChargeSupport.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums
 */
interface BookingChargeSupport
{
    /** @var string[] */
    public const SUPPORTS_CHARGE = [
        PaymentMethodTypes::ALI_PAY,
        PaymentMethodTypes::APPLE_PAY,
        PaymentMethodTypes::BANCONTACT,
        PaymentMethodTypes::CARDS,
        PaymentMethodTypes::EPS,
        PaymentMethodTypes::GIROPAY,
        PaymentMethodTypes::GOOGLE_PAY,
        PaymentMethodTypes::IDEAL,
        PaymentMethodTypes::PAYPAL,
        PaymentMethodTypes::PAYU,
        PaymentMethodTypes::PRZELEWY24,
        PaymentMethodTypes::POST_FINANCE_CARD,
        PaymentMethodTypes::POST_FINANCE_EFINANCE,
        PaymentMethodTypes::SOFORT,
        PaymentMethodTypes::TWINT,
        PaymentMethodTypes::UNZER_DIRECT_DEBIT,
        PaymentMethodTypes::UNZER_PREPAYMENT,
        PaymentMethodTypes::WECHATPAY
    ];
}
