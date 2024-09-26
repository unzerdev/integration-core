<?php

namespace Unzer\Core\BusinessLogic\ApiFacades;

/**
 * Class FacadeController.
 *
 * @package Unzer\Core\BusinessLogic\ApiFacades
 */
abstract class FacadeController
{
    protected function __construct()
    {
    }

    /**
     * Gets an Facade controller instance.
     *
     * @return self
     */
    abstract public static function get(): object;
}
