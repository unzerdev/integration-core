<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentRequest;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Processors\InlinePaymentProcessorInterface;

class InlinePaymentProcessorMock implements InlinePaymentProcessorInterface
{

    public string $mockProcessorUrl = '';

    /**
     * @param InlinePaymentRequest $inlineRequest
     * @param InlinePaymentCreateContext $context
     * @param PaymentMethodConfig|null $paymentMethodConfig
     * @return void
     */
    public function process(InlinePaymentRequest $inlineRequest, InlinePaymentCreateContext $context, ?PaymentMethodConfig $paymentMethodConfig)
    {
        $inlineRequest->setReturnUrl($this->mockProcessorUrl);
    }
}
