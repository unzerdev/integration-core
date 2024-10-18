<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Controller;


use Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Request\SavePaymentMapRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Response\PaymentMapGetResponse;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Response\PaymentMapSaveResponse;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;

/**
 * Class PaymentStatusMapController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Controller
 */
class PaymentStatusMapController
{
    /**
     * @var PaymentStatusMapService
     */
    private PaymentStatusMapService $paymentStatusMapService;

    /**
     * @param PaymentStatusMapService $paymentStatusMapService
     */
    public function __construct(PaymentStatusMapService $paymentStatusMapService)
    {
        $this->paymentStatusMapService = $paymentStatusMapService;
    }

    /**
     * @param SavePaymentMapRequest $paymentMapRequest
     *
     * @return PaymentMapSaveResponse
     */
    public function savePaymentStatusMap(SavePaymentMapRequest $paymentMapRequest): PaymentMapSaveResponse
    {
        $this->paymentStatusMapService->savePaymentStatusMappingSettings(
            $paymentMapRequest->getPaymentStatusMap()
        );

        return new PaymentMapSaveResponse();
    }

    /**
     * @return PaymentMapGetResponse
     */
    public function getPaymentStatusMap(): PaymentMapGetResponse
    {
        return new PaymentMapGetResponse($this->paymentStatusMapService->getPaymentStatusMap());
    }
}
