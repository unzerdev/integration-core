<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Interface PaymentPageProcessor
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
interface PaymentPageProcessor extends RequestProcessor
{
    public function process(Paypage $payPageRequest, PaymentPageCreateContext $context): void;
}
