<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\Processors\CustomerProcessor;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Customer;

/**
 * Class MockCustomerProcessor
 *
 * @package BusinessLogic\Common\Mocks
 */
class MockCustomerProcessor implements CustomerProcessor
{

    public function process(Customer $customer, PaymentPageCreateContext $context): void
    {
        $customer->setEmail('test@example.com');
    }
}
