<?php

namespace Unzer\Core\BusinessLogic\Domain\Translations\Exceptions;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class BaseTranslatableUnhandledException
 *
 * @package Unzer\Core\BusinessLogic\Domain\Translations\Model
 */
class BaseTranslatableUnhandledException extends BaseTranslatableException
{
    /**
     * @param Throwable $previous
     */
    public function __construct(Throwable $previous)
    {
        parent::__construct(
            new TranslatableLabel(
                'Unhandled error occurred: ' . $previous->getMessage(),
                'general.unhandled'
            ),
            $previous
        );
    }
}
