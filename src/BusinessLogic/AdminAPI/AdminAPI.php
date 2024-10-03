<?php

namespace Unzer\Core\BusinessLogic\AdminAPI;

use Unzer\Core\BusinessLogic\AdminAPI\Connection\Controller\ConnectionController;
use Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Controller\DisconnectController;
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
}
