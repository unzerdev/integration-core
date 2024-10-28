<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors\PaymentPageProcessorsRegistry;
use UnzerSDK\Resources\PaymentTypes\Paypage;

/**
 * Class Factory
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory
 */
class PaymentPageFactory
{
    public function create(PaymentPageCreateContext $context): Paypage
    {
        $payPage = $this->initializePayPage($context);
        foreach (PaymentPageProcessorsRegistry::getProcessors($context->getPaymentMethodType()) as $processor) {
            $processor->process($payPage, $context);
        }

        return $payPage;
    }

    protected function initializePayPage(PaymentPageCreateContext $context): Paypage
    {
        return (new Paypage(
            $context->getAmount()->getPriceInCurrencyUnits(),
            $context->getAmount()->getCurrency()->getIsoCode(),
            $context->getReturnUrl()
        ))->setOrderId($context->getOrderId());
    }
}
