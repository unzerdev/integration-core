<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Processors\PaymentPageProcessorsRegistry;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use UnzerSDK\Constants\PaypageCheckoutTypes;
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
    const EMBEDDED_PAYPAGE_TYPE = "embedded";

    private PaymentPageSettingsService $paymentPageSettingsService;

    /**
     * PaymentPageFactory constructor.
     *
     * @param PaymentPageSettingsService $paymentPageSettingsService
     */
    public function __construct(PaymentPageSettingsService $paymentPageSettingsService)
    {
        $this->paymentPageSettingsService = $paymentPageSettingsService;
    }

    /**
     * @param PaymentPageCreateContext $context
     * @param PaymentMethodConfig $paymentMethodConfig
     * @param Resources $resources
     *
     * @return Paypage
     */
    public function create(
        PaymentPageCreateContext $context,
        PaymentMethodConfig $paymentMethodConfig,
        Resources $resources
    ): Paypage {
        $payPage = $this->initializePayPage(
            $context,
            $paymentMethodConfig->getBookingMethod()->getBookingMethod(),
            $resources
        );

        foreach (PaymentPageProcessorsRegistry::getProcessors($context->getPaymentMethodType()) as $processor) {
            $processor->process($payPage, $context, $paymentMethodConfig);
        }

        return $payPage;
    }

    protected function initializePayPage(
        PaymentPageCreateContext $context,
        string $bookingMethod,
        Resources $resources
    ): Paypage {
        $paymentPageSettings = $this->paymentPageSettingsService->getPaymentPageSettings();

        $result = (new Paypage(
            $context->getAmount()->getPriceInCurrencyUnits(),
            $context->getAmount()->getCurrency()->getIsoCode(),
            $bookingMethod
        ))->setOrderId($context->getOrderId())
            ->setResources($resources);

        $url = $context->getReturnUrl();

        $urls = new Urls();
        $urls->setReturnSuccess($url)
            ->setReturnFailure($url)
            ->setReturnPending($url)
            ->setReturnCancel($url);

        $result->setUrls($urls);
        $result->setType(self::EMBEDDED_PAYPAGE_TYPE);
        $result->setCheckoutType(PaypageCheckoutTypes::PAYMENT_ONLY);

        return $paymentPageSettings ? $paymentPageSettings->inflate($result, $context->getLocale()) : $result;
    }
}
