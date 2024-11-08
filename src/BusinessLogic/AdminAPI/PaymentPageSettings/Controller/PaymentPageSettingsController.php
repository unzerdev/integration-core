<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request\PaymentPageSettingsRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response\PaymentPagePreviewResponse;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response\PaymentPageSettingsGetResponse;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response\PaymentPageSettingsPutResponse;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidImageUrlException;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class PaymentPageController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Controller
 */
class PaymentPageSettingsController
{
    /**
     * @var PaymentPageSettingsService $paymentPageSettingsService
     */
    private PaymentPageSettingsService $paymentPageSettingsService;

    /**
     * @param PaymentPageSettingsService $paymentPageSettingsService
     */
    public function __construct(PaymentPageSettingsService $paymentPageSettingsService)
    {
        $this->paymentPageSettingsService = $paymentPageSettingsService;
    }

    /**
     * @param PaymentPageSettingsRequest $paymentPageSettingsRequest
     *
     * @return PaymentPageSettingsPutResponse
     *
     * @throws InvalidImageUrlException
     */
    public function savePaymentPageSettings(
        PaymentPageSettingsRequest $paymentPageSettingsRequest
    ): PaymentPageSettingsPutResponse {
        $paymentPageSettings = $this->paymentPageSettingsService->savePaymentPageSettings(
            $paymentPageSettingsRequest->transformToDomainModel()
        );

        return new PaymentPageSettingsPutResponse($paymentPageSettings);
    }

    /**
     * @return PaymentPageSettingsGetResponse
     */
    public function getPaymentPageSettings(): PaymentPageSettingsGetResponse
    {
        return new PaymentPageSettingsGetResponse($this->paymentPageSettingsService->getPaymentPageSettings());
    }

    /**
     * @param PaymentPageSettingsRequest $pageSettingsRequest
     *
     * @return PaymentPagePreviewResponse
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidImageUrlException
     */
    public function getPaymentPagePreview(PaymentPageSettingsRequest $pageSettingsRequest): PaymentPagePreviewResponse
    {
        $paypage = $this->paymentPageSettingsService->createMockPaypage(
            $pageSettingsRequest->transformToDomainModel()
        );

        return new PaymentPagePreviewResponse($paypage);
    }
}
