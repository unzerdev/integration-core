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
    /**
     * @param PaymentContext $context
     *
     * @return ?Customer
     */
    public function create(PaymentContext $context): ?Customer
    {
        $customer = $this->initializeCustomer($context);
        foreach (CustomerProcessorsRegistry::getProcessors($context->getPaymentMethodType()) as $processor) {
            $processor->process($customer, $context);
        }

        return $customer->getEmail() ? $customer : null;
    }

    /**
     * @param PaymentContext $context
     *
     * @return Customer
     */
    protected function initializeCustomer(PaymentContext $context): Customer
    {
        return new Customer();
    }
}
