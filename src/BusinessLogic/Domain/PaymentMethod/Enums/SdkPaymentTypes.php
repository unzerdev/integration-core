<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

use UnzerSDK\Resources\PaymentTypes\Alipay;
use UnzerSDK\Resources\PaymentTypes\Applepay;
use UnzerSDK\Resources\PaymentTypes\Bancontact;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\EPS;
use UnzerSDK\Resources\PaymentTypes\Giropay;
use UnzerSDK\Resources\PaymentTypes\Googlepay;
use UnzerSDK\Resources\PaymentTypes\Ideal;
use UnzerSDK\Resources\PaymentTypes\Klarna;
use UnzerSDK\Resources\PaymentTypes\PaylaterDirectDebit;
use UnzerSDK\Resources\PaymentTypes\PaylaterInstallment;
use UnzerSDK\Resources\PaymentTypes\PaylaterInvoice;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\PayU;
use UnzerSDK\Resources\PaymentTypes\PostFinanceCard;
use UnzerSDK\Resources\PaymentTypes\PostFinanceEfinance;
use UnzerSDK\Resources\PaymentTypes\Prepayment;
use UnzerSDK\Resources\PaymentTypes\Przelewy24;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Resources\PaymentTypes\Sofort;
use UnzerSDK\Resources\PaymentTypes\Twint;
use UnzerSDK\Resources\PaymentTypes\Wechatpay;

/**
 * Class SdkPaymentTypes.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums
 */
interface SdkPaymentTypes
{
    /** @var string[] */
    public const PAYMENT_TYPES = [
        Alipay::class => PaymentMethodTypes::ALI_PAY,
        Applepay::class => PaymentMethodTypes::APPLE_PAY,
        Bancontact::class => PaymentMethodTypes::BANCONTACT,
        Card::class => PaymentMethodTypes::CARDS,
        EPS::class => PaymentMethodTypes::EPS,
        Giropay::class => PaymentMethodTypes::GIROPAY,
        Googlepay::class => PaymentMethodTypes::GOOGLE_PAY,
        Ideal::class => PaymentMethodTypes::IDEAL,
        Klarna::class => PaymentMethodTypes::KLARNA,
        Paypal::class => PaymentMethodTypes::PAYPAL,
        PayU::class => PaymentMethodTypes::PAYU,
        Przelewy24::class => PaymentMethodTypes::PRZELEWY24,
        PostFinanceCard::class => PaymentMethodTypes::POST_FINANCE_CARD,
        PostFinanceEfinance::class => PaymentMethodTypes::POST_FINANCE_EFINANCE,
        Sofort::class => PaymentMethodTypes::SOFORT,
        Twint::class => PaymentMethodTypes::TWINT,
        SepaDirectDebit::class => PaymentMethodTypes::UNZER_DIRECT_DEBIT,
        PaylaterDirectDebit::class => PaymentMethodTypes::DIRECT_DEBIT_SECURED,
        PaylaterInstallment::class => PaymentMethodTypes::UNZER_INSTALLMENT,
        PaylaterInvoice::class => PaymentMethodTypes::UNZER_INVOICE,
        Prepayment::class => PaymentMethodTypes::UNZER_PREPAYMENT,
        Wechatpay::class => PaymentMethodTypes::WECHATPAY
    ];
}
