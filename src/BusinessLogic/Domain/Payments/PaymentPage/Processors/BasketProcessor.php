<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Models\PaymentContext;
use Unzer\Core\BusinessLogic\Domain\Payments\Common\Processors\RequestProcessor;
use UnzerSDK\Resources\Basket;

/**
 * Interface PaymentPageProcessor
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
interface BasketProcessor extends RequestProcessor
{
    public function process(Basket $basket, PaymentContext $context): void;
}
