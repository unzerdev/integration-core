<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

/**
 * Interface PaymentMethodTypes.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums
 */
interface PaymentMethodTypes
{
    /** @var string */
    public const ALI_PAY = 'alipay';

    /** @var string */
    public const APPLE_PAY = 'applepay';

    /** @var string */
    public const BANCONTACT = 'bancontact';

    /** @var string */
    public const CARDS = 'card';

    /** @var string */
    public const GIROPAY = 'giropay';

    /** @var string */
    public const GOOGLE_PAY = 'googlepay';

    /** @var string */
    public const IDEAL = 'ideal';

    /** @var string */
    public const KLARNA = 'klarna';

    /** @var string */
    public const PAYPAL = 'paypal';

    /** @var string */
    public const PAYU = 'payu';

    /** @var string */
    public const PRZELEWY24 = 'przelewy24';

    /** @var string */
    public const POST_FINANCE_CARD = 'post-finance-card';

    /** @var string */
    public const POST_FINANCE_EFINANCE = 'post-finance-efinance';

    /** @var string */
    public const SOFORT = 'sofort';

    /** @var string */
    public const TWINT = 'twint';

    /** @var string */
    public const UNZER_DIRECT_DEBIT = 'sepa-direct-debit';

    /** @var string */
    public const DIRECT_DEBIT_SECURED = 'paylater-direct-debit';

    /** @var string */
    public const UNZER_INSTALLMENT = 'paylater-installment';

    /** @var string */
    public const UNZER_INVOICE = 'paylater-invoice';

    /** @var string */
    public const UNZER_PREPAYMENT = 'prepayment';

    /** @var string */
    public const UNZER_PAYPAGE = 'unzer-paypage';

    /** @var string */
    public const WECHATPAY = 'wechatpay';

    /** @var string */
    public const EPS = 'EPS';

    /** @var string  */
    public const DIRECT_BANK_TRANSFER = 'openbanking-pis';

    /** @var string  */
    public const CLICK_TO_PAY = 'clicktopay';

    /** @var string  */
    public const WERO = 'wero';

    /** @var string[] */
    public const PAYMENT_TYPES = [
        PaymentMethodTypes::ALI_PAY,
        PaymentMethodTypes::APPLE_PAY,
        PaymentMethodTypes::BANCONTACT,
        PaymentMethodTypes::CARDS,
        PaymentMethodTypes::EPS,
        PaymentMethodTypes::GIROPAY,
        PaymentMethodTypes::GOOGLE_PAY,
        PaymentMethodTypes::IDEAL,
        PaymentMethodTypes::KLARNA,
        PaymentMethodTypes::PAYPAL,
        PaymentMethodTypes::PAYU,
        PaymentMethodTypes::PRZELEWY24,
        PaymentMethodTypes::POST_FINANCE_CARD,
        PaymentMethodTypes::POST_FINANCE_EFINANCE,
        PaymentMethodTypes::SOFORT,
        PaymentMethodTypes::TWINT,
        PAymentMethodTypes::DIRECT_BANK_TRANSFER,
        PaymentMethodTypes::UNZER_DIRECT_DEBIT,
        PaymentMethodTypes::DIRECT_DEBIT_SECURED,
        PaymentMethodTypes::UNZER_INSTALLMENT,
        PaymentMethodTypes::UNZER_INVOICE,
        PaymentMethodTypes::UNZER_PREPAYMENT,
        PaymentMethodTypes::UNZER_PAYPAGE,
        PaymentMethodTypes::WECHATPAY,
        PaymentMethodTypes::CLICK_TO_PAY,
        PaymentMethodTypes::WERO
    ];
}
