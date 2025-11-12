<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Processors;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Processors\RequestProcessor;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentRequest;

interface InlinePaymentProcessorInterface extends RequestProcessor
{
    public function process(InlinePaymentRequest $inlineRequest, InlinePaymentCreateContext $context, ?PaymentMethodConfig $paymentMethodConfig);
}