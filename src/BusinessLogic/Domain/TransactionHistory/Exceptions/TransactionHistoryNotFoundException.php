<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions;

use Throwable;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class TransactionHistoryNotFoundException.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions
 */
class TransactionHistoryNotFoundException extends BaseTranslatableException
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
