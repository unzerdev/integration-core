<?php

namespace Unzer\Core\BusinessLogic;

use Unzer\Core\BusinessLogic\AdminAPI\Connection\Controller\ConnectionController;
use Unzer\Core\BusinessLogic\AdminAPI\Country\Controller\CountryController;
use Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Controller\DisconnectController;
use Unzer\Core\BusinessLogic\AdminAPI\Language\Controller\LanguageController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Controller\PaymentMethodsController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Controller\PaymentPageSettingsController;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Controller\StoresController;
use Unzer\Core\BusinessLogic\AdminAPI\Version\Controller\VersionController;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Controller\CheckoutPaymentMethodsController;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Repositories\PaymentMethodConfigRepository;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Repositories\PaymentPageSettingsRepository;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookData;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Repositories\WebhookDataRepository;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Unzer\Core\BusinessLogic\Domain\Integration\Country\CountryService;
use Unzer\Core\BusinessLogic\Domain\Integration\Language\LanguageService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Versions\VersionService;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Store\StoreService as IntegrationStoreService;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Stores\Services\StoreService;
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

        ServiceRegister::registerService(
            StoreService::class,
            new SingleInstance(static function () {
                return new StoreService(
                    ServiceRegister::getService(IntegrationStoreService::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentPageSettingsService::class,
            new SingleInstance(static function () {
                return new PaymentPageSettingsService(
                    ServiceRegister::getService(PaymentPageSettingsRepositoryInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentMethodService::class,
            new SingleInstance(static function () {
                return new PaymentMethodService(
                    UnzerFactory::getInstance()->makeUnzerAPI(),
                    ServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class)
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

        ServiceRegister::registerService(
            PaymentPageSettingsRepositoryInterface::class,
            new SingleInstance(static function () {
                return new PaymentPageSettingsRepository(
                    RepositoryRegistry::getRepository(PaymentPageSettings::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentMethodConfigRepositoryInterface::class,
            new SingleInstance(static function () {
                return new PaymentMethodConfigRepository(
                    RepositoryRegistry::getRepository(PaymentMethodConfig::getClassName()),
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

        ServiceRegister::registerService(
            StoresController::class,
            new SingleInstance(static function () {
                return new StoresController(
                    ServiceRegister::getService(StoreService::class),
                    ServiceRegister::getService(ConnectionService::class)
                );
            })
        );

        ServiceRegister::registerService(
            CountryController::class,
            new SingleInstance(static function () {
                return new CountryController(
                    ServiceRegister::getService(CountryService::class)
                );
            })
        );

        ServiceRegister::registerService(
            LanguageController::class,
            new SingleInstance(static function () {
                return new LanguageController(
                    ServiceRegister::getService(LanguageService::class)
                );
            })
        );

        ServiceRegister::registerService(
            VersionController::class,
            new SingleInstance(static function () {
                return new VersionController(
                    ServiceRegister::getService(VersionService::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentPageSettingsController::class,
            new SingleInstance(static function () {
                return new PaymentPageSettingsController(
                    ServiceRegister::getService(PaymentPageSettingsService::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentMethodsController::class,
            new SingleInstance(static function () {
                return new PaymentMethodsController(ServiceRegister::getService(PaymentMethodService::class));
            })
        );

        ServiceRegister::registerService(
            CheckoutPaymentMethodsController::class,
            new SingleInstance(static function () {
                return new CheckoutPaymentMethodsController();
            })
        );
    }
}
