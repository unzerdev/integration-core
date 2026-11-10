<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions;

/**
 * Class CustomerCreationException
 *
 * @package Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions
 */
class CustomerCreationException extends PaymentResourceCreationException
{
    /**
     * @return string
     */
    protected static function stepMessage(): string
    {
        return 'Unzer customer could not be created';
    }

    /**
     * @return string
     */
    protected static function translationCode(): string
    {
        return 'payment.customerCreationFailed';
    }
}
