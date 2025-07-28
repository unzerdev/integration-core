<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Processors;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Processors\RequestProcessor;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentResponse;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;

interface InlinePaymentProcessorInterface extends RequestProcessor
{
    public function execute(
        InlinePaymentCreateContext $context,
        PaymentMethodConfig $config,
        Resources $resources
    ): InlinePaymentResponse;
}