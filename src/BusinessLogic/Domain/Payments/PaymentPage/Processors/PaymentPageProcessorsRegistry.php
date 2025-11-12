<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Processors\AbstractRequestProcessorsRegistry;
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
