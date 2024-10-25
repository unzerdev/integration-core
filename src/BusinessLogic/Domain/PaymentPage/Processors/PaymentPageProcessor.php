<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\PaymentTypes\Paypage;

/**
 * Interface PaymentPageProcessor
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
interface PaymentPageProcessor
{
    public function process(Paypage $payPageRequest, PaymentPageCreateContext $context): void;
}
