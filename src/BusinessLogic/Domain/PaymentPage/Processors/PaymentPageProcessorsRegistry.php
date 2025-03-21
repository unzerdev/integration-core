<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors;

use Unzer\Core\Infrastructure\Singleton;

/**
 * Class Registry
 *
 * @template T of PaymentPageProcessor
 *
 * @method static PaymentPageProcessor[] getProcessors(string $type)
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
class PaymentPageProcessorsRegistry extends AbstractRequestProcessorsRegistry
{
    protected static ?Singleton $instance = null;
}
