<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Exceptions;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class InvalidTranslatableArrayException.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Exceptions
 */
class InvalidTranslatableArrayException extends BaseTranslatableException
{
    /**
     * @param TranslatableLabel $translatableLabel
     * @param Throwable|null $previous
     */
    public function __construct(TranslatableLabel $translatableLabel, Throwable $previous = null)
    {
        $this->code = 404;

        parent::__construct($translatableLabel, $previous);
    }
}
