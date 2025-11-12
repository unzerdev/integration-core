<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\Customer\Processors;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Processors\AbstractRequestProcessorsRegistry;
use Unzer\Core\Infrastructure\Singleton;

/**
 * Class Registry
 *
 * @template T of CustomerProcessor
 *
 * @method static CustomerProcessor[] getProcessors(string $type)
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
class CustomerProcessorsRegistry extends AbstractRequestProcessorsRegistry
{
    protected static ?Singleton $instance = null;
}
