<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors\PaymentPageProcessorsRegistry;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Urls;
use UnzerSDK\Resources\V2\Paypage;

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

    public function create(PaymentPageCreateContext $context, string $bookingMethod, Resources $resources): Paypage
    {
        $payPage = $this->initializePayPage($context, $bookingMethod, $resources);
        foreach (PaymentPageProcessorsRegistry::getProcessors($context->getPaymentMethodType()) as $processor) {
            $processor->process($payPage, $context);
        }

        return $payPage;
    }

    protected function initializePayPage(PaymentPageCreateContext $context, string $bookingMethod, Resources $resources): Paypage
    {
        $paymentPageSettings = $this->paymentPageSettingsService->getPaymentPageSettings();
        $url = $context->getReturnUrl();

        $result = (new Paypage(
            $context->getAmount()->getPriceInCurrencyUnits(),
            $context->getAmount()->getCurrency()->getIsoCode(),
            $bookingMethod
        ))->setOrderId($context->getOrderId())
        ->setResources($resources);

        $urls = new Urls();
        $urls->setReturnSuccess($url)
        ->setReturnFailure($url)
        ->setReturnPending($url)
        ->setReturnCancel($url);

        $result->setUrls($urls);

        return $paymentPageSettings ? $paymentPageSettings->inflate($result, $context->getLocale()) : $result;
    }
}
