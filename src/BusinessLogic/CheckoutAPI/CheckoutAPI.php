<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI;

use Unzer\Core\BusinessLogic\ApiFacades\Aspects\ErrorHandlingAspect;
use Unzer\Core\BusinessLogic\ApiFacades\Aspects\StoreContextAspect;
use Unzer\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Controller\CheckoutPaymentMethodsController;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller\CheckoutPaymentPageController;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller\CheckoutInlinePaymentController;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;

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
    public function paymentMethods(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(CheckoutPaymentMethodsController::class);
    }

    /**
     * @param string $storeId
     *
     * @return CheckoutPaymentPageController
     */
    public function paymentPage(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(CheckoutPaymentPageController::class);
    }

    public function inlinePayment(string $storeId)
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(CheckoutInlinePaymentController::class);
    }
}
