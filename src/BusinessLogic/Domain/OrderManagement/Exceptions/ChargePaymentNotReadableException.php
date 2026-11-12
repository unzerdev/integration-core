<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions;

use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;

/**
 * Class ChargePaymentNotReadableException.
 *
 * The Unzer payment could not be read before a charge. Thrown by integrations that override
 * OrderManagementService::fetchTransactionHistoryForCharge() to tell an Unzer error apart from
 * "nothing to charge"; the Unzer error is the previous exception.
 *
 * @package Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions
 */
class ChargePaymentNotReadableException extends BaseTranslatableException
{
}
