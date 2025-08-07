<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller;

use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\InlinePaymentCreateRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\DataBag;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentResponse;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Services\InlinePaymentService;
use UnzerSDK\Exceptions\UnzerApiException;

class CheckoutInlinePaymentController
{
    private InlinePaymentService $inlinePaymentService;

    private ConnectionService $connectionService;

    /**
     * CheckoutPaymentPageController constructor.
     *
     * @param InlinePaymentService $inlinePaymentService
     * @param ConnectionService $connectionService
     */
    public function __construct(InlinePaymentService $inlinePaymentService, ConnectionService $connectionService)
    {
        $this->inlinePaymentService = $inlinePaymentService;
        $this->connectionService = $connectionService;
    }

    /**
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     * @throws PaymentConfigNotFoundException
     */
    public function create(InlinePaymentCreateRequest $request): InlinePaymentResponse
    {
        return $this->inlinePaymentService->create(new InlinePaymentCreateContext(
            $request->getPaymentMethodType(),
            $request->getOrderId(),
            $request->getAmount(),
            $request->getReturnUrl(),
            new DataBag($request->getSessionData()),
            $request->getLocale()
        ));
    }
}
