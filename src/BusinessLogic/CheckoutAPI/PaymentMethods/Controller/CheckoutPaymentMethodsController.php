<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Controller;

use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request\PaymentMethodsRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Response\PaymentMethodsResponse;

/**
 * Class CheckoutPaymentMethodsController.
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Controller
 */
class CheckoutPaymentMethodsController
{
    /**
     * @param PaymentMethodsRequest $request
     *
     * @return PaymentMethodsResponse
     */
    public function getAvailablePaymentMethods(PaymentMethodsRequest $request): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse();
    }
}
