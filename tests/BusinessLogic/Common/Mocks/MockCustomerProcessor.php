<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\Processors\CustomerProcessor;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use UnzerSDK\Resources\Customer;

/**
 * Class MockCustomerProcessor
 *
 * @package BusinessLogic\Common\Mocks
 */
class MockCustomerProcessor implements CustomerProcessor
{

    public function process(Customer $customer, PaymentContext $context): void
    {
        $customer
            ->setEmail('test@example.com')
            ->setCustomerId($context->getSessionData()->get('customerId', 'test-customer-123'));
    }
}
