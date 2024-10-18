<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums;

/**
 * Interface PaymentStatus.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums
 */
interface PaymentStatus
{
    public const PAID = 'Paid';
    public const UNPAID = 'Unpaid';
    public const PARTIAL_REFUND = 'Partial Refund';
    public const FULL_REFUND = 'Full Refund';
    public const CHARGEBACK = 'Charge back';
    public const DECLINED = 'Declined';
    public const COLLECTION = 'Collection';
    public const CANCELLED = 'Cancelled';
}
