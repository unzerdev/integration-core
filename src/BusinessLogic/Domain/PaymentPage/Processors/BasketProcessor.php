<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Basket;

/**
 * Interface PaymentPageProcessor
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors
 */
interface BasketProcessor extends RequestProcessor
{
    public function process(Basket $basket, PaymentPageCreateContext $context): void;
}
