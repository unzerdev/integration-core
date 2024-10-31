<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors\PaymentPageProcessorsRegistry;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use UnzerSDK\Resources\PaymentTypes\Paypage;

/**
 * Class Factory
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory
 */
class PaymentPageFactory
{
    private PaymentPageSettingsService $paymentPageSettingsService;

    /**
     * PaymentPageFactory constructor.
     * @param PaymentPageSettingsService $paymentPageSettingsService
     */
    public function __construct(PaymentPageSettingsService $paymentPageSettingsService)
    {
        $this->paymentPageSettingsService = $paymentPageSettingsService;
    }

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
        $paymentPageSettings = $this->paymentPageSettingsService->getPaymentPageSettings();
        $result = (new Paypage(
            $context->getAmount()->getPriceInCurrencyUnits(),
            $context->getAmount()->getCurrency()->getIsoCode(),
            $context->getReturnUrl()
        ))->setOrderId($context->getOrderId());

        return $paymentPageSettings ? $paymentPageSettings->inflate($result) : $result;
    }
}
