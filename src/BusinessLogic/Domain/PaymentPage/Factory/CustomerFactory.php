<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors\CustomerProcessorsRegistry;
use UnzerSDK\Resources\Customer;

/**
 * Class Factory
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory
 */
class CustomerFactory
{
    public function create(PaymentPageCreateContext $context): ?Customer
    {
        $customer = $this->initializeCustomer($context);
        foreach (CustomerProcessorsRegistry::getProcessors($context->getPaymentMethodType()) as $processor) {
            $processor->process($customer, $context);
        }

        return $customer->getCustomerId() ? $customer : null;
    }

    protected function initializeCustomer(PaymentPageCreateContext $context): Customer
    {
        return new Customer();
    }
}
