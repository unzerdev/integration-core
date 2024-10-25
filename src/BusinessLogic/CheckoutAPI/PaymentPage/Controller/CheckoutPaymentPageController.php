<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller;

use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response\PaymentPageResponse;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response\PaymentStateResponse;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\DataBag;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
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
            $this->paymentPageService->create(new PaymentPageCreateContext(
                $request->getPaymentMethodType(),
                $request->getOrderId(),
                $request->getAmount(),
                $request->getReturnUrl(),
                new DataBag($request->getSessionData())
            ))
        );
    }

    public function getPaymentState(string $orderId): PaymentStateResponse
    {
        return new PaymentStateResponse($this->paymentPageService->getPaymentState($orderId));
    }
}
