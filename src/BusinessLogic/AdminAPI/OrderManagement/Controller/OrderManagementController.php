<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\CancellationRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\ChargeRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\RefundRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response\CancellationResponse;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response\ChargeResponse;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response\RefundResponse;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Services\OrderManagementService;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class OrderManagementController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Controller
 */
class OrderManagementController
{
    /** @var OrderManagementService $orderManagementService */
    private OrderManagementService $orderManagementService;

    /**
     * @param OrderManagementService $orderManagementService
     */
    public function __construct(OrderManagementService $orderManagementService)
    {
        $this->orderManagementService = $orderManagementService;
    }

    /**
     * @param RefundRequest $refundRequest
     *
     * @return RefundResponse
     *
     * @throws UnzerApiException
     * @throws CurrencyMismatchException
     * @throws ConnectionSettingsNotFoundException
     */
    public function refund(RefundRequest $refundRequest): RefundResponse
    {
        $this->orderManagementService->refundOrder($refundRequest->getOrderId(), $refundRequest->getAmount());

        return new RefundResponse();
    }

    /**
     * @param ChargeRequest $chargeRequest
     *
     * @return ChargeResponse
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function charge(ChargeRequest $chargeRequest): ChargeResponse
    {
        $this->orderManagementService->chargeOrder($chargeRequest->getOrderId(), $chargeRequest->getAmount());

        return new ChargeResponse();
    }

    /**
     * @param CancellationRequest $cancellationRequest
     *
     * @return CancellationResponse
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function cancel(CancellationRequest $cancellationRequest): CancellationResponse
    {
        $this->orderManagementService->cancelOrder($cancellationRequest->getOrderId(), $cancellationRequest->getAmount());

        return new CancellationResponse();
    }
}
