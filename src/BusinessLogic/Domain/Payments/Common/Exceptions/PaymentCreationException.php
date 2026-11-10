<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions;

/**
 * Class PaymentCreationException
 *
 * @package Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions
 */
class PaymentCreationException extends PaymentResourceCreationException
{
    /**
     * @return string
     */
    protected static function stepMessage(): string
    {
        return 'Unzer payment could not be created';
    }

    /**
     * @return string
     */
    protected static function translationCode(): string
    {
        return 'payment.paymentCreationFailed';
    }
}
