<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions;

/**
 * Class MetadataCreationException
 *
 * @package Unzer\Core\BusinessLogic\Domain\Payments\Common\Exceptions
 */
class MetadataCreationException extends PaymentResourceCreationException
{
    /**
     * @return string
     */
    protected static function stepMessage(): string
    {
        return 'Unzer metadata could not be created';
    }

    /**
     * @return string
     */
    protected static function translationCode(): string
    {
        return 'payment.metadataCreationFailed';
    }
}
