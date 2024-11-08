<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class InvalidImageUrlException.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions
 */
class InvalidImageUrlException extends BaseTranslatableException
{
    /**
     * @param TranslatableLabel $translatableLabel
     * @param Throwable|null $previous
     */
    public function __construct(TranslatableLabel $translatableLabel, Throwable $previous = null)
    {
        $this->code = 400;

        parent::__construct($translatableLabel, $previous);
    }
}
