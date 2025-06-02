<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Interface PaymentPageProcessor
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
interface PaymentPageProcessor extends RequestProcessor
{
    /**
     * @param Paypage $payPageRequest
     * @param PaymentPageCreateContext $context
     * @param PaymentMethodConfig $paymentMethodConfiguration
     *
     * @return void
     */
    public function process(
        Paypage $payPageRequest,
        PaymentPageCreateContext $context,
        PaymentMethodConfig $paymentMethodConfiguration
    ): void;
}
