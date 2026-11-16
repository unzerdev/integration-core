<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Models;

/**
 * Class CancelSkipReason.
 *
 * Why a cancellation of the authorization was not sent to Unzer.
 *
 * @package Unzer\Core\BusinessLogic\Domain\OrderManagement\Models
 */
final class CancelSkipReason
{
    /** @var string No transaction history was found for the order, or it could not be read. */
    public const PAYMENT_NOT_FOUND = 'payment_not_found';

    /** @var string The transaction history misses amounts or the payment state (checked only with an amount). */
    public const INVALID_HISTORY = 'invalid_history';

    /** @var string The payment is canceled (checked only with an amount). */
    public const PAYMENT_CANCELED = 'payment_canceled';

    /** @var string The payment is still in state create (checked only with an amount). */
    public const PAYMENT_NOT_CREATED = 'payment_not_created';

    /** @var string The requested amount is above the remaining amount (checked only with an amount). */
    public const AMOUNT_EXCEEDS_REMAINING = 'amount_exceeds_remaining';

    /** @var string The payment has no authorization to cancel. */
    public const NO_AUTHORIZATION = 'no_authorization';

    /** @var string The integration found the payment already canceled at Unzer. */
    public const ALREADY_CANCELED = 'already_canceled';

    /** @var string An integration override of isCancellationNecessary() declined the cancellation without a reason. */
    public const NOT_NECESSARY = 'not_necessary';
}
