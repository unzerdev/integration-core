<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request\PaymentPageSettingsRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response\PaymentPageSettingsGetResponse;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response\PaymentPageSettingsPutResponse;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;

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
     * @throws InvalidTranslatableArrayException
     */
    public function savePaymentPageSettings(
        PaymentPageSettingsRequest $paymentPageSettingsRequest
    ): PaymentPageSettingsPutResponse {
        $this->paymentPageSettingsService->savePaymentPageSettings(
            $paymentPageSettingsRequest->transformToDomainModel()
        );

        return new PaymentPageSettingsPutResponse();
    }

    /**
     * @return PaymentPageSettingsGetResponse
     */
    public function getPaymentPageSettings(): PaymentPageSettingsGetResponse
    {
        return new PaymentPageSettingsGetResponse($this->paymentPageSettingsService->getPaymentPageSettings());
    }

}