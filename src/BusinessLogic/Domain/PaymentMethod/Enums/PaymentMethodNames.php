<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

/**
 * Interface PaymentMethodNames. Maps payment method type to payment method name.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums
 */
interface PaymentMethodNames
{
    /** @var string[] */
    public const PAYMENT_METHOD_NAMES = [
        PaymentMethodTypes::ALI_PAY => 'Alipay',
        PaymentMethodTypes::APPLE_PAY => 'Apple Pay',
        PaymentMethodTypes::BANCONTACT => 'Bancontact',
        PaymentMethodTypes::CARDS => 'Cards and Click to Pay',
        PaymentMethodTypes::EPS => 'EPS',
        PaymentMethodTypes::GIROPAY => 'Giropay',
        PaymentMethodTypes::GOOGLE_PAY => 'Google Pay',
        PaymentMethodTypes::IDEAL => 'iDEAL',
        PaymentMethodTypes::KLARNA => 'Klarna',
        PaymentMethodTypes::PAYPAL => 'PayPal',
        PaymentMethodTypes::PAYU => 'PayU',
        PaymentMethodTypes::PRZELEWY24 => 'Przelewy24',
        PaymentMethodTypes::POST_FINANCE_CARD => 'Post Finance Card',
        PaymentMethodTypes::POST_FINANCE_EFINANCE => 'Post Finance eFinance',
        PaymentMethodTypes::SOFORT => 'Sofort',
        PaymentMethodTypes::TWINT => 'TWINT',
        PaymentMethodTypes::UNZER_DIRECT_DEBIT => 'Unzer Direct Debit',
        PaymentMethodTypes::DIRECT_DEBIT_SECURED => 'Direct Debit Secured',
        PaymentMethodTypes::UNZER_INSTALLMENT => 'Unzer Installment',
        PaymentMethodTypes::UNZER_INVOICE => 'Unzer Invoice',
        PaymentMethodTypes::UNZER_PREPAYMENT => 'Unzer Prepayment',
        PaymentMethodTypes::UNZER_PAYPAGE => 'Unzer Paypage',
        PaymentMethodTypes::WECHATPAY => 'WeChat Pay',
        PaymentMethodTypes::DIRECT_BANK_TRANSFER => 'Direct Bank Transfer',
    ];

    /** @var string  */
    public const DEFAULT_PAYMENT_METHOD_NAME = 'Unzer payment';

    /** @var string  */
    public const DEFAULT_PAYMENT_METHOD_DESCRIPTION = 'When you select this payment method, a secure pop-up window will be displayed to complete the transaction.';
}
