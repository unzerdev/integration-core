<?php

namespace Unzer\Core\BusinessLogic;

use Unzer\Core\BusinessLogic\AdminAPI\Connection\Controller\ConnectionController;
use Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Controller\DisconnectController;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookData;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Repositories\WebhookDataRepository;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\Infrastructure\BootstrapComponent as BaseBootstrapComponent;
use Unzer\Core\Infrastructure\ORM\RepositoryRegistry;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\BusinessLogic\Bootstrap\SingleInstance;

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

        ServiceRegister::registerService(StoreContext::class, static function () {
            return StoreContext::getInstance();
        });

        ServiceRegister::registerService(
            ConnectionService::class,
            new SingleInstance(static function () {
                return new ConnectionService(
                    ServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    ServiceRegister::getService(WebhookDataRepositoryInterface::class),
                    ServiceRegister::getService(EncryptorInterface::class),
                    ServiceRegister::getService(WebhookUrlServiceInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            DisconnectService::class,
            new SingleInstance(static function () {
                return new DisconnectService(
                    UnzerFactory::getInstance()->makeUnzerAPI(),
                    ServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    ServiceRegister::getService(WebhookDataRepositoryInterface::class)
                );
            })
        );
    }

    /**
     * @return void
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();

        ServiceRegister::registerService(
            ConnectionSettingsRepositoryInterface::class,
            new SingleInstance(static function () {
                return new ConnectionSettingsRepository(
                    RepositoryRegistry::getRepository(ConnectionSettings::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            WebhookDataRepositoryInterface::class,
            new SingleInstance(static function () {
                return new WebhookDataRepository(
                    RepositoryRegistry::getRepository(WebhookData::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );
    }

    /**
     * @return void
     */
    private static function initControllers(): void
    {
        ServiceRegister::registerService(
            ConnectionController::class,
            new SingleInstance(static function () {
                return new ConnectionController(
                    ServiceRegister::getService(ConnectionService::class)
                );
            })
        );

        ServiceRegister::registerService(
            DisconnectController::class,
            new SingleInstance(static function () {
                return new DisconnectController(
                    ServiceRegister::getService(DisconnectService::class)
                );
            })
        );
    }
}
