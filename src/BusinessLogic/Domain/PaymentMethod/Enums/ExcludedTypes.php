<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethod;

interface ExcludedTypes
{
    /** @var string[] */
    public const EXCLUDED_METHOD_NAMES = [
        PaymentMethodTypes::ALI_PAY => 'alipay',
        PaymentMethodTypes::APPLE_PAY => 'applepay',
        PaymentMethodTypes::BANCONTACT => 'bancontact',
        PaymentMethodTypes::CARDS => 'cards',
        PaymentMethodTypes::GOOGLE_PAY => 'googlepay',
        PaymentMethodTypes::IDEAL => 'ideal',
        PaymentMethodTypes::KLARNA => 'klarna',
        PaymentMethodTypes::PAYPAL => 'paypal',
        PaymentMethodTypes::PAYU => 'payu',
        PaymentMethodTypes::PRZELEWY24 => 'przelewy24',
        PaymentMethodTypes::POST_FINANCE_CARD => 'pfcard',
        PaymentMethodTypes::POST_FINANCE_EFINANCE => 'pfefinance',
        PaymentMethodTypes::TWINT => 'twint',
        PaymentMethodTypes::UNZER_DIRECT_DEBIT => 'sepaDirectDebit',
        PaymentMethodTypes::DIRECT_DEBIT_SECURED => 'paylaterDirectDebit',
        PaymentMethodTypes::UNZER_INSTALLMENT => 'paylaterInstallment',
        PaymentMethodTypes::UNZER_PREPAYMENT => 'prepayment',
        PaymentMethodTypes::WECHATPAY => 'wechatpay',
        PaymentMethodTypes::EPS => "eps",
        PaymentMethodTypes::DIRECT_BANK_TRANSFER => "openbankingpis",
        PaymentMethodTypes::UNZER_INVOICE => "paylaterInvoice",
    ];
}
