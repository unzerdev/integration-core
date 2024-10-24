<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller;

use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response\PaymentPageResponse;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Services\PaymentPageService;

/**
 * Class CheckoutPaymentPageController
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller
 */
class CheckoutPaymentPageController
{
    private PaymentPageService $paymentPageService;

    /**
     * CheckoutPaymentPageController constructor.
     * @param PaymentPageService $paymentPageService
     */
    public function __construct(PaymentPageService $paymentPageService)
    {
        $this->paymentPageService = $paymentPageService;
    }

    public function create(PaymentPageCreateRequest $request): PaymentPageResponse
    {
        return new PaymentPageResponse(
            $this->paymentPageService->create(
                $request->getPaymentMethodType(),
                $request->getOrderId(),
                $request->getAmount(),
                $request->getReturnUrl()
            )
        );
    }
}
