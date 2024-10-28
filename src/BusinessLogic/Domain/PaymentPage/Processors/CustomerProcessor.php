<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Customer;

/**
 * Interface PaymentPageProcessor
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
interface CustomerProcessor extends RequestProcessor
{
    public function process(Customer $customer, PaymentPageCreateContext $context): void;
}
