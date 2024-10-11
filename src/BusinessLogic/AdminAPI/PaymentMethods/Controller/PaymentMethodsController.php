<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response\PaymentMethodsResponse;

/**
 * Class PaymentMethodsController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Controller
 */
class PaymentMethodsController
{
    /**
     * @return PaymentMethodsResponse
     */
    public function getPaymentMethods(): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse();
    }
}
