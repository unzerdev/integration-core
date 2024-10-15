<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI;

use Unzer\Core\BusinessLogic\ApiFacades\Aspects\ErrorHandlingAspect;
use Unzer\Core\BusinessLogic\ApiFacades\Aspects\StoreContextAspect;
use Unzer\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Controller\CheckoutPaymentMethodsController;

/**
 * Class AdminAPI. Integrations should use this class for communicating with Admin API.
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI
 */
class CheckoutAPI
{
    private function __construct()
    {
    }

    /**
     * @return CheckoutAPI
     */
    public static function get(): object
    {
        return Aspects::run(new ErrorHandlingAspect())->beforeEachMethodOfInstance(new CheckoutAPI());
    }

    /**
     * @param string $storeId
     *
     * @return CheckoutPaymentMethodsController
     */
    public function connection(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(CheckoutPaymentMethodsController::class);
    }
}
