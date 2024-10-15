<?php

namespace Unzer\Core\BusinessLogic\Domain\Country\Exceptions;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class InvalidCountryArrayException.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Country\Exceptions
 */
class InvalidCountryArrayException extends BaseTranslatableException
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
