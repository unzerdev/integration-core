<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class PaymentConfigNotFoundException.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions
 */
class PaymentConfigNotFoundException extends BaseTranslatableException
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
