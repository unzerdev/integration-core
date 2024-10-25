<?php

namespace Unzer\Core\BusinessLogic\WebhookAPI;

use Unzer\Core\BusinessLogic\ApiFacades\Aspects\ErrorHandlingAspect;
use Unzer\Core\BusinessLogic\ApiFacades\Aspects\StoreContextAspect;
use Unzer\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use Unzer\Core\BusinessLogic\WebhookAPI\Handler\Controller\WebhookHandlerController;

/**
 * Class WebhookAPI.
 *
 * @package Unzer\Core\BusinessLogic\WebhookAPI
 */
class WebhookAPI
{
    private function __construct()
    {
    }

    /**
     * @return WebhookAPI
     */
    public static function get(): object
    {
        return Aspects::run(new ErrorHandlingAspect())->beforeEachMethodOfInstance(new WebhookAPI());
    }

    /**
     * @param string $storeId
     *
     * @return WebhookHandlerController
     */
    public function connection(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(WebhookHandlerController::class);
    }
}
