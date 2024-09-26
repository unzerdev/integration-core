<?php

namespace Unzer\Core\BusinessLogic\AdminAPI;

use Unzer\Core\BusinessLogic\ApiFacades\Aspects\ErrorHandlingAspect;
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
}
