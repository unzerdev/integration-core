<?php

namespace Unzer\Core\BusinessLogic;

use Unzer\Core\BusinessLogic\AdminAPI\Connection\Controller\ConnectionController;
use Unzer\Core\BusinessLogic\AdminAPI\Country\Controller\CountryController;
use Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Controller\DisconnectController;
use Unzer\Core\BusinessLogic\AdminAPI\Language\Controller\LanguageController;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Controller\OrderManagementController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Controller\PaymentMethodsController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Controller\PaymentPageSettingsController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Controller\PaymentStatusMapController;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Controller\StoresController;
use Unzer\Core\BusinessLogic\AdminAPI\Transaction\Controller\TransactionController;
use Unzer\Core\BusinessLogic\AdminAPI\Version\Controller\VersionController;
use Unzer\Core\BusinessLogic\Bootstrap\SingleInstance;
use Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Controller\CheckoutInlinePaymentController;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Controller\CheckoutPaymentMethodsController;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller\CheckoutPaymentPageController;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Repositories\PaymentMethodConfigRepository;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Repositories\PaymentPageSettingsRepository;
use Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Entities\PaymentStatusMap;
use Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Repositories\PaymentStatusMapRepository;
use Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory;
use Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Repositories\TransactionHistoryRepository;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookSettings;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Repositories\WebhookSettingsRepository;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Unzer\Core\BusinessLogic\Domain\Integration\Country\CountryService;
use Unzer\Core\BusinessLogic\Domain\Integration\Currency\CurrencyServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Language\LanguageService;
use Unzer\Core\BusinessLogic\Domain\Integration\Order\OrderServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\MetadataProvider;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\Processors\CustomerProcessor;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\Processors\LineItemsProcessor;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentStatusMap\PaymentStatusMapServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Store\StoreService as IntegrationStoreService;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Versions\VersionService;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Services\OrderManagementService;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Payments\Customer\Factory\CustomerFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\Customer\Processors\CustomerProcessorsRegistry;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Factory\InlinePaymentFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Services\InlinePaymentService;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Strategy\InlinePaymentStrategyFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Factory\BasketFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Factory\PaymentPageFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Processors\BasketProcessorsRegistry;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Processors\ExcludeTypesProcessor;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Processors\PaymentPageProcessorsRegistry;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Services\PaymentPageService;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentType\Factory\PaymentTypeFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentType\Services\PaymentTypeService;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;
use Unzer\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Listeners\TransactionSyncListener;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Service\TransactionSynchronizerService;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Webhook\Services\WebhookService;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\BusinessLogic\WebhookAPI\Handler\Controller\WebhookHandlerController;
use Unzer\Core\Infrastructure\BootstrapComponent as BaseBootstrapComponent;
use Unzer\Core\Infrastructure\ORM\RepositoryRegistry;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\TaskEvents\TickEvent;
use Unzer\Core\Infrastructure\Utility\Events\EventBus;

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
        static::initRequestProcessors();
    }

    /**
     * @return void
     */
    protected static function initServices(): void
    {
        parent::initServices();

        ServiceRegister::registerService(UnzerFactory::class, new SingleInstance(static function () {
            return new UnzerFactory();
        }));

        ServiceRegister::registerService(StoreContext::class, static function () {
            return StoreContext::getInstance();
        });

        ServiceRegister::registerService(
            ConnectionService::class,
            new SingleInstance(static function () {
                return new ConnectionService(
                    ServiceRegister::getService(UnzerFactory::class),
                    ServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    ServiceRegister::getService(WebhookSettingsRepositoryInterface::class),
                    ServiceRegister::getService(EncryptorInterface::class),
                    ServiceRegister::getService(WebhookUrlServiceInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            DisconnectService::class,
            new SingleInstance(static function () {
                return new DisconnectService(
                    ServiceRegister::getService(ConnectionService::class),
                    ServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class),
                    ServiceRegister::getService(PaymentPageSettingsRepositoryInterface::class),
                    ServiceRegister::getService(PaymentStatusMapRepositoryInterface::class),
                    ServiceRegister::getService(TransactionHistoryRepositoryInterface::class),
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
                    ServiceRegister::getService(PaymentPageSettingsRepositoryInterface::class),
                    ServiceRegister::getService(UploaderService::class),
                    ServiceRegister::getService(UnzerFactory::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentMethodService::class,
            new SingleInstance(static function () {
                return new PaymentMethodService(
                    ServiceRegister::getService(UnzerFactory::class),
                    ServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class),
                    ServiceRegister::getService(CurrencyServiceInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentPageService::class,
            new SingleInstance(static function () {
                return new PaymentPageService(
                    ServiceRegister::getService(UnzerFactory::class),
                    ServiceRegister::getService(PaymentMethodService::class),
                    ServiceRegister::getService(TransactionHistoryService::class),
                    ServiceRegister::getService(PaymentPageFactory::class),
                    ServiceRegister::getService(CustomerFactory::class),
                    ServiceRegister::getService(BasketFactory::class),
                    ServiceRegister::getService(MetadataProvider::class)
                );
            })
        );

        ServiceRegister::registerService(
            InlinePaymentService::class,
            new SingleInstance(static function () {
                return new InlinePaymentService(
                    ServiceRegister::getService(UnzerFactory::class),
                    ServiceRegister::getService(InlinePaymentStrategyFactory::class),
                    ServiceRegister::getService(PaymentMethodService::class),
                    ServiceRegister::getService(TransactionHistoryService::class),
                    ServiceRegister::getService(InlinePaymentFactory::class),
                    ServiceRegister::getService(CustomerFactory::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentTypeService::class,
            new SingleInstance(static function () {
                return new PaymentTypeService(
                    ServiceRegister::getService(UnzerFactory::class),
                    ServiceRegister::getService(PaymentTypeFactory::class),
                );
            })
        );

        ServiceRegister::registerService(
            PaymentStatusMapService::class,
            new SingleInstance(static function () {
                return new PaymentStatusMapService(
                    ServiceRegister::getService(PaymentStatusMapRepositoryInterface::class),
                    ServiceRegister::getService(PaymentStatusMapServiceInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            InlinePaymentStrategyFactory::class,
            new SingleInstance(static function () {
                return new InlinePaymentStrategyFactory();
            })
        );

        ServiceRegister::registerService(
            TransactionHistoryService::class,
            new SingleInstance(static function () {
                return new TransactionHistoryService(
                    ServiceRegister::getService(TransactionHistoryRepositoryInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            OrderManagementService::class,
            new SingleInstance(static function () {
                return new OrderManagementService(
                    ServiceRegister::getService(UnzerFactory::class),
                    ServiceRegister::getService(TransactionHistoryService::class)
                );
            })
        );

        ServiceRegister::registerService(
            TransactionSynchronizerService::class,
            new SingleInstance(static function () {
                return new TransactionSynchronizerService(
                    ServiceRegister::getService(UnzerFactory::class),
                    ServiceRegister::getService(TransactionHistoryService::class),
                    ServiceRegister::getService(OrderServiceInterface::class),
                    ServiceRegister::getService(PaymentStatusMapService::class)
                );
            })
        );

        ServiceRegister::registerService(
            WebhookService::class,
            new SingleInstance(static function () {
                return new WebhookService(
                    ServiceRegister::getService(UnzerFactory::class),
                    ServiceRegister::getService(TransactionSynchronizerService::class)
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
            WebhookSettingsRepositoryInterface::class,
            new SingleInstance(static function () {
                return new WebhookSettingsRepository(
                    RepositoryRegistry::getRepository(WebhookSettings::getClassName()),
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

        ServiceRegister::registerService(
            PaymentStatusMapRepositoryInterface::class,
            new SingleInstance(static function () {
                return new PaymentStatusMapRepository(
                    RepositoryRegistry::getRepository(PaymentStatusMap::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            TransactionHistoryRepositoryInterface::class,
            new SingleInstance(static function () {
                return new TransactionHistoryRepository(
                    RepositoryRegistry::getRepository(TransactionHistory::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );
    }

    /**
     * @return void
     */
    protected static function initControllers(): void
    {
        ServiceRegister::registerService(
            ConnectionController::class,
            new SingleInstance(static function () {
                return new ConnectionController(
                    ServiceRegister::getService(ConnectionService::class),
                    ServiceRegister::getService(DisconnectService::class)
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
                return new PaymentMethodsController(
                    ServiceRegister::getService(PaymentMethodService::class),
                    ServiceRegister::getService(CurrencyServiceInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            CheckoutPaymentMethodsController::class,
            new SingleInstance(static function () {
                return new CheckoutPaymentMethodsController(ServiceRegister::getService(PaymentMethodService::class));
            })
        );

        ServiceRegister::registerService(
            CheckoutPaymentPageController::class,
            new SingleInstance(static function () {
                return new CheckoutPaymentPageController(ServiceRegister::getService(PaymentPageService::class),
                ServiceRegister::getService(ConnectionService::class));
            })
        );

        ServiceRegister::registerService(
            CheckoutInlinePaymentController::class,
            new SingleInstance(static function () {
                return new CheckoutInlinePaymentController(ServiceRegister::getService(InlinePaymentService::class));
            })
        );

        ServiceRegister::registerService(
            PaymentStatusMapController::class,
            new SingleInstance(static function () {
                return new PaymentStatusMapController(ServiceRegister::getService(PaymentStatusMapService::class));
            })
        );

        ServiceRegister::registerService(
            OrderManagementController::class,
            new SingleInstance(static function () {
                return new OrderManagementController(ServiceRegister::getService(OrderManagementService::class));
            })
        );

        ServiceRegister::registerService(
            WebhookHandlerController::class,
            new SingleInstance(static function () {
                return new WebhookHandlerController(ServiceRegister::getService(WebhookService::class));
            })
        );

        ServiceRegister::registerService(
            TransactionController::class,
            new SingleInstance(static function () {
                return new TransactionController(ServiceRegister::getService(TransactionHistoryService::class));
            })
        );
    }

    protected static function initRequestProcessors(): void
    {
        ServiceRegister::registerService(
            PaymentTypeFactory::class,
            new SingleInstance(static function () {
                return new PaymentTypeFactory();
            })
        );

        ServiceRegister::registerService(
            InlinePaymentFactory::class,
            new SingleInstance(static function () {
                return new InlinePaymentFactory(ServiceRegister::getService(PaymentTypeService::class));
            })
        );

        ServiceRegister::registerService(
            PaymentPageFactory::class,
            new SingleInstance(static function () {
                return new PaymentPageFactory(ServiceRegister::getService(PaymentPageSettingsService::class));
            })
        );
        ServiceRegister::registerService(
            CustomerFactory::class,
            new SingleInstance(static function () {
                return new CustomerFactory();
            })
        );
        ServiceRegister::registerService(
            BasketFactory::class,
            new SingleInstance(static function () {
                return new BasketFactory(ServiceRegister::getService(PaymentMethodService::class));
            })
        );

        PaymentPageProcessorsRegistry::registerGlobal(ExcludeTypesProcessor::class);
        ServiceRegister::registerService(ExcludeTypesProcessor::class, new SingleInstance(static function () {
            return new ExcludeTypesProcessor(
                ServiceRegister::getService(UnzerFactory::class)
            );
        }));

        CustomerProcessorsRegistry::registerGlobal(CustomerProcessor::class);
        BasketProcessorsRegistry::registerGlobal(LineItemsProcessor::class);

    }

    /**
     * @return void
     */
    protected static function initEvents()
    {
        parent::initEvents();

        /** @var EventBus $eventBus */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);

        $eventBus->when(
            TickEvent::class,
            static function () {
                (new TransactionSyncListener())->handle();
            }
        );
    }
}
