<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Exceptions;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class PrivateKeyInvalidException.
 *
 * @package Unzer\Core\BusinessLogic\UnzerAPI\Connection\Exceptions
 */
class PrivateKeyInvalidException extends BaseTranslatableException
{
    /**
     * @param TranslatableLabel $translatableLabel
     * @param Throwable|null $previous
     */
    public function __construct(TranslatableLabel $translatableLabel, Throwable $previous = null)
    {
        $this->code = 401;

        parent::__construct($translatableLabel, $previous);
    }
}
