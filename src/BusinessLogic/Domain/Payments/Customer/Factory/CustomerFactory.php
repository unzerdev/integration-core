<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\Customer\Factory;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use Unzer\Core\BusinessLogic\Domain\Payments\Customer\Processors\CustomerProcessorsRegistry;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Customer;

/**
 * Class Factory
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory
 */
class CustomerFactory
{
    public function create(PaymentContext $context): ?Customer
    {
        $customer = $this->initializeCustomer($context);
        foreach (CustomerProcessorsRegistry::getProcessors($context->getPaymentMethodType()) as $processor) {
            $processor->process($customer, $context);
        }

        return $customer->getCustomerId() ? $customer : null;
    }

    protected function initializeCustomer(PaymentContext $context): Customer
    {
        return new Customer();
    }
}
