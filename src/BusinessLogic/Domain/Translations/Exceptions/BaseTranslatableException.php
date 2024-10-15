<?php

namespace Unzer\Core\BusinessLogic\Domain\Translations\Exceptions;

use Exception;
use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class BaseTranslatableException
 *
 * @package Unzer\Core\BusinessLogic\Domain\Translations\Model
 */
class BaseTranslatableException extends Exception
{
    /**
     * @var TranslatableLabel
     */
    protected TranslatableLabel $translatableLabel;

    /**
     * @param TranslatableLabel $translatableLabel
     * @param Throwable|null $previous
     */
    public function __construct(TranslatableLabel $translatableLabel, Throwable $previous = null)
    {
        parent::__construct($translatableLabel->getMessage(), $this->code, $previous);

        $this->translatableLabel = $translatableLabel;
    }

    /**
     * @return TranslatableLabel
     */
    public function getTranslatableLabel(): TranslatableLabel
    {
        return $this->translatableLabel;
    }
}
