<?php

namespace Unzer\Core\Infrastructure;

use Unzer\Core\Infrastructure\Configuration\ConfigurationManager;
use Unzer\Core\Infrastructure\Http\CurlHttpClient;
use Unzer\Core\Infrastructure\Http\HttpClient;
use Unzer\Core\Infrastructure\Utility\Events\EventBus;
use Unzer\Core\Infrastructure\Utility\GuidProvider;
use Unzer\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class BootstrapComponent.
 *
 * @package Unzer\Core\Infrastructure
 */
class BootstrapComponent
{
    /**
     * Initializes infrastructure components.
     */
    public static function init()
    {
        static::initServices();
        static::initRepositories();
        static::initEvents();
    }

    /**
     * Initializes services and utilities.
     */
    protected static function initServices()
    {
        ServiceRegister::registerService(
            ConfigurationManager::CLASS_NAME,
            function () {
                return ConfigurationManager::getInstance();
            }
        );
        ServiceRegister::registerService(
            TimeProvider::CLASS_NAME,
            function () {
                return TimeProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            GuidProvider::CLASS_NAME,
            function () {
                return GuidProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            EventBus::CLASS_NAME,
            function () {
                return EventBus::getInstance();
            }
        );

        ServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () {
                return new CurlHttpClient();
            }
        );
    }

    /**
     * Initializes repositories.
     */
    protected static function initRepositories()
    {
    }

    /**
     * Initializes events.
     */
    protected static function initEvents()
    {
    }
}
