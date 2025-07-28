<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentRequest;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Processors\InlinePaymentProcessorRegistry;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Processors\PaymentPageProcessorsRegistry;
use UnzerSDK\Constants\PaypageCheckoutTypes;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Urls;
use UnzerSDK\Resources\V2\Paypage;

class InlinePaymentFactory
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
     * @param InlinePaymentCreateContext $context
     * @param PaymentMethodConfig $paymentMethodConfig
     * @param Resources $resources
     *
     * @return InlinePaymentRequest
     */
    public function create(
        InlinePaymentCreateContext $context,
        PaymentMethodConfig $paymentMethodConfig,
        Resources $resources
    ): InlinePaymentRequest {
        $inlineRequest = $this->initializeRequest(
            $context,
            $paymentMethodConfig->getBookingMethod()->getBookingMethod(),
            $resources
        );

        foreach (InlinePaymentProcessorRegistry::getProcessors($context->getPaymentMethodType()) as $processor) {
            $processor->process($inlineRequest, $context, $paymentMethodConfig);
        }

        return $inlineRequest;
    }

    protected function initializeRequest(
        InlinePaymentCreateContext $context,
        string $bookingMethod,
        Resources $resources
    ): InlinePaymentRequest {
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