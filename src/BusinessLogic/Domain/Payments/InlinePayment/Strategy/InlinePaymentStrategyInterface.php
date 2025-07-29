<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Strategy;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentResponse;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;

interface InlinePaymentStrategyInterface
{
    public function execute(
        InlinePaymentCreateContext $context,
        ?PaymentMethodConfig $config,
        Resources $resources
    ): InlinePaymentResponse;
}