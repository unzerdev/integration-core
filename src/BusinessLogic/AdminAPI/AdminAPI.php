<?php

namespace Unzer\Core\BusinessLogic\AdminAPI;

use Unzer\Core\BusinessLogic\AdminAPI\Connection\Controller\ConnectionController;
use Unzer\Core\BusinessLogic\AdminAPI\Country\Controller\CountryController;
use Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Controller\DisconnectController;
use Unzer\Core\BusinessLogic\AdminAPI\Language\Controller\LanguageController;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Controller\OrderManagementController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Controller\PaymentMethodsController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Controller\PaymentPageSettingsController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Controller\PaymentStatusMapController;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Controller\StoresController;
use Unzer\Core\BusinessLogic\AdminAPI\Version\Controller\VersionController;
use Unzer\Core\BusinessLogic\ApiFacades\Aspects\ErrorHandlingAspect;
use Unzer\Core\BusinessLogic\ApiFacades\Aspects\StoreContextAspect;
use Unzer\Core\BusinessLogic\Bootstrap\Aspect\Aspects;

/**
 * Class AdminAPI. Integrations should use this class for communicating with Admin API.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI
 */
class AdminAPI
{
    private function __construct()
    {
    }

    /**
     * @return AdminAPI
     */
    public static function get(): object
    {
        return Aspects::run(new ErrorHandlingAspect())->beforeEachMethodOfInstance(new AdminAPI());
    }

    /**
     * @param string $storeId
     *
     * @return ConnectionController
     */
    public function connection(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(ConnectionController::class);
    }

    /**
     * @param string $storeId
     *
     * @return DisconnectController
     */
    public function disconnect(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(DisconnectController::class);
    }

    /**
     * @return StoresController
     */
    public function stores(): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->beforeEachMethodOfService(StoresController::class);
    }

    /**
     * @param string $storeId
     *
     * @return CountryController
     */
    public function countries(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(CountryController::class);
    }

    /**
     * @param string $storeId
     *
     * @return LanguageController
     */
    public function languages(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(LanguageController::class);
    }

    /**
     * @return VersionController
     */
    public function version(): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->beforeEachMethodOfService(VersionController::class);
    }

    /**
     * @param string $storeId
     *
     * @return PaymentPageSettingsController
     */
    public function paymentPageSettings(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(PaymentPageSettingsController::class);
    }

    /**
     * @param string $storeId
     *
     * @return PaymentMethodsController
     */
    public function paymentMethods(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(PaymentMethodsController::class);
    }

    /**
     * @param string $storeId
     *
     * @return PaymentStatusMapController
     */
    public function paymentStatusMap(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(PaymentStatusMapController::class);
    }

    /**
     * @param string $storeId
     *
     * @return OrderManagementController
     */
    public function order(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(OrderManagementController::class);
    }
}
