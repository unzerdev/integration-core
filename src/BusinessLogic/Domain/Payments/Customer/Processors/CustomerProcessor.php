<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\Customer\Processors;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Processors\RequestProcessor;
use UnzerSDK\Resources\Customer;

/**
 * Interface PaymentPageProcessor
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
interface CustomerProcessor extends RequestProcessor
{
    public function process(Customer $customer, PaymentContext $context): void;
}
