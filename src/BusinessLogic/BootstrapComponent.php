<?php

namespace Unzer\Core\BusinessLogic;

use Unzer\Core\Infrastructure\BootstrapComponent as BaseBootstrapComponent;

/**
 * Class BootstrapComponent.
 *
 * @package Unzer\Core\BusinessLogic
 */
class BootstrapComponent extends BaseBootstrapComponent
{
    /**
     * @return void
     */
    public static function init(): void
    {
        parent::init();

        static::initControllers();
    }

    /**
     * @return void
     */
    protected static function initServices(): void
    {
        parent::initServices();
    }

    /**
     * @return void
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();
    }

    /**
     * @return void
     */
    private static function initControllers(): void
    {
    }
}
