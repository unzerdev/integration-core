<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Exceptions;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class ConnectionSettingsNotFoundException.
 *
 * @package Unzer\Core\BusinessLogic\UnzerAPI\Exceptions
 */
class ConnectionSettingsNotFoundException extends BaseTranslatableException
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
