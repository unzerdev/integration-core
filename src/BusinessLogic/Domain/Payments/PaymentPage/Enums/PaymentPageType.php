<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Enums;

/**
 * Interface PaymentPageType.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Enums
 */
interface PaymentPageType
{
    public const EMBEDDED = 'embedded';
    public const HOSTED = 'hosted';
}