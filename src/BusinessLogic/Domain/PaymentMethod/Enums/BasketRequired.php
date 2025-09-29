<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums;

/**
 * Interface BasketRequired.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums
 */
interface BasketRequired
{
    /** @var string[] */
    public const BASKET_REQUIRED = [
        PaymentMethodTypes::DIRECT_DEBIT_SECURED,
        PaymentMethodTypes::KLARNA
    ];
}
