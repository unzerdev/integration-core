<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Processors;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Processors\AbstractRequestProcessorsRegistry;
use Unzer\Core\Infrastructure\Singleton;

/**
 * Class Registry
 *
 * @template T of InlinePaymentProcessorInterface
 *
 * @method static InlinePaymentProcessorInterface[] getProcessors(string $type)
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
class InlinePaymentProcessorRegistry extends AbstractRequestProcessorsRegistry
{
    protected static ?Singleton $instance = null;
}