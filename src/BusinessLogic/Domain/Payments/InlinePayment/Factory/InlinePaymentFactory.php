<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Factory;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentRequest;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Processors\InlinePaymentProcessorRegistry;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentType\Services\PaymentTypeService;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;

class InlinePaymentFactory
{

    private PaymentTypeService $paymentTypeService;

    /**
     * PaymentPageFactory constructor.
     *
     * @param PaymentTypeService $paymentTypeService
     */
    public function __construct(PaymentTypeService $paymentTypeService)
    {
        $this->paymentTypeService = $paymentTypeService;
    }

    /**
     * @param InlinePaymentCreateContext $context
     * @param PaymentMethodConfig|null $paymentMethodConfig
     * @param Resources $resources
     *
     * @return InlinePaymentRequest
     */
    public function create(
        InlinePaymentCreateContext $context,
        ?PaymentMethodConfig $paymentMethodConfig,
        Resources $resources
    ): InlinePaymentRequest {
        $bookingMethod = $paymentMethodConfig ? $paymentMethodConfig->getBookingMethod() : BookingMethod::charge()->getBookingMethod();
        $inlineRequest = $this->initializeRequest(
            $context,
            $bookingMethod,
            $resources
        );

        foreach (InlinePaymentProcessorRegistry::getProcessors($context->getPaymentMethodType()) as $processor) {
            $processor->process($inlineRequest, $context, $paymentMethodConfig);
        }

        return $inlineRequest;
    }

    /**
     * @param InlinePaymentCreateContext $context
     * @param string $bookingMethod
     * @param Resources $resources
     * @return InlinePaymentRequest
     */
    protected function initializeRequest(
        InlinePaymentCreateContext $context,
        string $bookingMethod,
        Resources $resources
    ): InlinePaymentRequest {

        $type = $this->paymentTypeService->create($context);

        return new InlinePaymentRequest($context->getAmount(), $context->getReturnUrl(), $type);
    }
}