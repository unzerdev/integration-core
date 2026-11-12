<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Models;

/**
 * Class ChargeSkipReason.
 *
 * Why a charge was not sent to Unzer.
 *
 * @package Unzer\Core\BusinessLogic\Domain\OrderManagement\Models
 */
final class ChargeSkipReason
{
    /** @var string No transaction history was found for the order. */
    public const PAYMENT_NOT_FOUND = 'payment_not_found';

    /** @var string The transaction history misses amounts or the payment state. */
    public const INVALID_HISTORY = 'invalid_history';

    /** @var string The payment is canceled. */
    public const PAYMENT_CANCELED = 'payment_canceled';

    /** @var string The payment is still in state create. */
    public const PAYMENT_NOT_CREATED = 'payment_not_created';

    /** @var string Nothing remains to be charged on the payment. */
    public const NOTHING_REMAINING = 'nothing_remaining';

    /** @var string The requested amount is above the remaining amount. */
    public const AMOUNT_EXCEEDS_REMAINING = 'amount_exceeds_remaining';

    /** @var string The payment has no authorization to charge. */
    public const NO_AUTHORIZATION = 'no_authorization';

    /** @var string The integration found the amount already charged. */
    public const ALREADY_CHARGED = 'already_charged';

    /** @var string An integration override of isChargeNecessary() declined the charge without a reason. */
    public const NOT_NECESSARY = 'not_necessary';
}
