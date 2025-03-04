<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

interface ExcludedTypes
{
    /** @var string[] */
    public const EXCLUDED_METHOD_NAMES = [
        'alipay' => 'alipay',
        'applepay' => 'applepay',
        'bancontact' => 'bancontact',
        'card' => 'cards',
        'giropay' => 'giropay',
        'googlepay' => 'googlepay',
        'ideal' => 'ideal',
        'klarna' => 'klarna',
        'paypal' => 'paypal',
        'payu' => 'payu',
        'przelewy24' => 'przelewy24',
        'post-finance-card' => 'pfcard',
        'post-finance-efinance' => 'pfefinance',
        'sofort' => 'sofort',
        'twint' => 'twint',
        'sepa-direct-debit' => 'sepaDirectDebit',
        'paylater-direct-debit' => 'paylaterDirectDebit',
        'paylater-installment' => 'paylaterInstallment',
        'paylater-invoice' => 'paylaterInvoice',
        'prepayment' => 'prepayment',
        'wechatpay' => 'wechatpay',
    ];
}
