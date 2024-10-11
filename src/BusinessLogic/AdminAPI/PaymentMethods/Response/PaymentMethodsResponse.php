<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;

/**
 * Class PaymentMethodsResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response
 */
class PaymentMethodsResponse extends Response
{

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            [
                'type' => 'alipay',
                'name' => 'Alipay',
                'description' => 'Alipay description',
                'enabled' => true
            ],
            [
                'type' => 'applepay',
                'name' => 'Apple Pay',
                'description' => 'Apple Pay description',
                'enabled' => true
            ],
            [
                'type' => 'bancontact',
                'name' => 'Bancontact',
                'description' => 'Bancontact description',
                'enabled' => false
            ],
            [
                'type' => 'card',
                'name' => 'Card',
                'description' => 'Card description',
                'enabled' => true
            ],
            [
                'type' => 'eps',
                'name' => 'EPS',
                'description' => 'EPS description',
                'enabled' => false
            ],
            [
                'type' => 'giropay',
                'name' => 'Giropay',
                'description' => 'Giropay description',
                'enabled' => true
            ],
            [
                'type' => 'googlepay',
                'name' => 'Google Pay',
                'description' => 'Google Pay description',
                'enabled' => true
            ],
            [
                'type' => 'ideal',
                'name' => 'Ideal',
                'description' => 'Ideal description',
                'enabled' => true
            ],
            [
                'type' => 'klarna',
                'name' => 'Klarna',
                'description' => 'Klarna description',
                'enabled' => false
            ],
            [
                'type' => 'paylater-direct-debit',
                'name' => 'PaylaterDirectDebit',
                'description' => 'PaylaterDirectDebit description',
                'enabled' => true
            ],
            [
                'type' => 'paylater-installment',
                'name' => 'PaylaterInstallment',
                'description' => 'PaylaterInstallment description',
                'enabled' => true
            ],
            [
                'type' => 'paylater-invoice',
                'name' => 'PaylaterInvoice',
                'description' => 'PaylaterInvoice description',
                'enabled' => false
            ],
            [
                'type' => 'paypal',
                'name' => 'Paypal',
                'description' => 'paypal description',
                'enabled' => true
            ],
            [
                'type' => 'payu',
                'name' => 'Payu',
                'description' => 'Payu description',
                'enabled' => true
            ],
            [
                'type' => 'post-finance-card',
                'name' => 'Post Finance Card',
                'description' => 'Post Finance Card description',
                'enabled' => true
            ],
            [
                'type' => 'post-finance-efinance',
                'name' => 'Post Finance E-Finance',
                'description' => 'Post Finance E-Finance description',
                'enabled' => true
            ],
            [
                'type' => 'prepayment',
                'name' => 'Unzer prepayment',
                'description' => 'Unzer description',
                'enabled' => true
            ],
            [
                'type' => 'przelewy24',
                'name' => 'Przelewy24',
                'description' => 'Przelewy24 description',
                'enabled' => false
            ],
            [
                'type' => 'sepa-direct-debit',
                'name' => 'SepaDirectDebit',
                'description' => 'SepaDirectDebit description',
                'enabled' => true
            ],
            [
                'type' => 'sofort',
                'name' => 'Sofort',
                'description' => 'Sofort description',
                'enabled' => true
            ],
            [
                'type' => 'twint',
                'name' => 'Twint',
                'description' => 'Twint description',
                'enabled' => false
            ],
            [
                'type' => 'wechatpay',
                'name' => 'Wechatpay',
                'description' => 'Wechatpay description',
                'enabled' => true
            ],
            [
                'type' => '',
                'name' => '',
                'description' => '',
                'enabled' => true
            ],
        ];
    }
}
