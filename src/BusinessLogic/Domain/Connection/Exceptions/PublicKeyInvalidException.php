<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Exceptions;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class PublicKeyInvalidException.
 *
 * @package Unzer\Core\BusinessLogic\UnzerAPI\Connection\Exceptions
 */
class PublicKeyInvalidException extends BaseTranslatableException
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
