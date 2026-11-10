<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions;

/**
 * Class PaypageCreationException
 *
 * @package Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions
 */
class PaypageCreationException extends PaymentResourceCreationException
{
    /**
     * @return string
     */
    protected static function stepMessage(): string
    {
        return 'Unzer payment page could not be created';
    }

    /**
     * @return string
     */
    protected static function translationCode(): string
    {
        return 'payment.paypageCreationFailed';
    }
}
