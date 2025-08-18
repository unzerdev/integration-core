<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Controller;

use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Controller\CommonFlowControllerInterface;
use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Request\CommonFlowRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Response\CommonFlowResponse;
use Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Request\InlinePaymentCreateRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\DataBag;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Services\InlinePaymentService;
use UnzerSDK\Exceptions\UnzerApiException;

class CheckoutInlinePaymentController implements CommonFlowControllerInterface
{
    private InlinePaymentService $inlinePaymentService;

    /**
     * CheckoutPaymentPageController constructor.
     *
     * @param InlinePaymentService $inlinePaymentService
     */
    public function __construct(InlinePaymentService $inlinePaymentService)
    {
        $this->inlinePaymentService = $inlinePaymentService;
    }

    /**
     * @param InlinePaymentCreateRequest $request
     * @return CommonFlowResponse
     * @throws ConnectionSettingsNotFoundException
     * @throws PaymentConfigNotFoundException
     * @throws UnzerApiException
     */
    public function create(CommonFlowRequest $request): CommonFlowResponse
    {
        return $this->inlinePaymentService->create(new InlinePaymentCreateContext(
            $request->getPaymentMethodType(),
            $request->getOrderId(),
            $request->getAmount(),
            $request->getReturnUrl(),
            new DataBag($request->getSessionData()),
            $request->getLocale(),
            $request->getBookingMethod()
        ));
    }
}
