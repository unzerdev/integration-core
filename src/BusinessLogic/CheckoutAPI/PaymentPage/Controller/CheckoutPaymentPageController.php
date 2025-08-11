<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller;

use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Controller\CommonFlowControllerInterface;
use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Request\CommonFlowRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Response\CommonFlowResponse;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response\PaymentPageResponse;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response\PaymentStateResponse;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\DataBag;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Services\PaymentPageService;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class CheckoutPaymentPageController
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller
 */
class CheckoutPaymentPageController implements CommonFlowControllerInterface
{
    private PaymentPageService $paymentPageService;

    private ConnectionService $connectionService;

    /**
     * CheckoutPaymentPageController constructor.
     *
     * @param PaymentPageService $paymentPageService
     * @param ConnectionService $connectionService
     */
    public function __construct(PaymentPageService $paymentPageService, ConnectionService $connectionService)
    {
        $this->paymentPageService = $paymentPageService;
        $this->connectionService = $connectionService;
    }

    /**
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     * @throws PaymentConfigNotFoundException
     */
    public function create(CommonFlowRequest $request): CommonFlowResponse
    {
        $connectionData = $this->connectionService->getConnectionSettings()->getActiveConnectionData();

        return new PaymentPageResponse(
            $this->paymentPageService->create(new PaymentPageCreateContext(
                $request->getPaymentMethodType(),
                $request->getOrderId(),
                $request->getAmount(),
                $request->getReturnUrl(),
                new DataBag($request->getSessionData()),
                $request->getLocale()
            )),
            $connectionData->getPublicKey()

        );
    }

    public function getPaymentState(string $orderId): PaymentStateResponse
    {
        return new PaymentStateResponse($this->paymentPageService->getPaymentState($orderId));
    }
}
