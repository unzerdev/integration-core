<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Controller;

use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request\PaymentMethodsRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Response\PaymentMethodsResponse;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class CheckoutPaymentMethodsController.
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Controller
 */
class CheckoutPaymentMethodsController
{
    /**
     * @var PaymentMethodService
     */
    private PaymentMethodService $paymentMethodService;

    /**
     * @param PaymentMethodService $paymentMethodService
     */
    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }

    /**
     * @param PaymentMethodsRequest $request
     *
     * @return PaymentMethodsResponse
     *
     * @throws UnzerApiException
     */
    public function getAvailablePaymentMethods(PaymentMethodsRequest $request): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse(
            $this->paymentMethodService->getPaymentMethodsForCheckout(
                $request->getAmount(),
                $request->getBillingCountry()
            ), $request->getLocale()
        );
    }
}
