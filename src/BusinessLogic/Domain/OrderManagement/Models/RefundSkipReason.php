<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Models;

/**
 * Class RefundSkipReason.
 *
 * Why a refund was not sent to Unzer.
 *
 * @package Unzer\Core\BusinessLogic\Domain\OrderManagement\Models
 */
final class RefundSkipReason
{
    /** @var string No transaction history was found for the order, or it could not be read. */
    public const PAYMENT_NOT_FOUND = 'payment_not_found';

    /** @var string The transaction history misses amounts or the payment state. */
    public const INVALID_HISTORY = 'invalid_history';

    /** @var string The payment is still pending: nothing was charged that could be refunded. */
    public const PAYMENT_PENDING = 'payment_pending';

    /** @var string The payment is canceled. */
    public const PAYMENT_CANCELED = 'payment_canceled';

    /** @var string The payment is still in state create. */
    public const PAYMENT_NOT_CREATED = 'payment_not_created';

    /** @var string The requested amount is above what is still refundable on the charges. */
    public const AMOUNT_EXCEEDS_REFUNDABLE = 'amount_exceeds_refundable';

    /** @var string The integration found the amount already refunded. */
    public const ALREADY_REFUNDED = 'already_refunded';

    /** @var string An integration override of isRefundNecessary() declined the refund without a reason. */
    public const NOT_NECESSARY = 'not_necessary';
}
