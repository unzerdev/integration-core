<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

/**
 * Interface RefundViaPayment.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums
 */
interface RefundViaPayment
{
    /** @var string[] */
    public const REFUND_VIA_PAYMENT = [
        PaymentMethodTypes::UNZER_INSTALLMENT,
        PaymentMethodTypes::UNZER_INVOICE,
        PaymentMethodTypes::DIRECT_DEBIT_SECURED,
        PaymentMethodTypes::KLARNA,
        PaymentMethodTypes::PAYU
    ];
}
