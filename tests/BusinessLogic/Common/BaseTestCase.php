<?php

namespace Unzer\Core\Tests\BusinessLogic\Common;

use PHPUnit\Framework\TestCase;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Controller\ConnectionController;
use Unzer\Core\BusinessLogic\AdminAPI\Country\Controller\CountryController;
use Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Controller\DisconnectController;
use Unzer\Core\BusinessLogic\AdminAPI\Language\Controller\LanguageController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Controller\PaymentMethodsController;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Controller\PaymentPageSettingsController;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Controller\StoresController;
use Unzer\Core\BusinessLogic\AdminAPI\Version\Controller\VersionController;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Controller\CheckoutPaymentMethodsController;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Controller\CheckoutPaymentPageController;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Repositories\PaymentMethodConfigRepository;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings as PaymentPageSettingsEntity;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Repositories\PaymentPageSettingsRepository;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Repositories\WebhookDataRepository;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Unzer\Core\BusinessLogic\Domain\Integration\Country\CountryService;
use Unzer\Core\BusinessLogic\Domain\Integration\Currency\CurrencyServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Language\LanguageService;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Versions\VersionService;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Services\PaymentPageService;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use Unzer\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\Infrastructure\Http\HttpClient;
use Unzer\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Unzer\Core\Infrastructure\Logger\Logger;
use Unzer\Core\Infrastructure\Logger\LoggerConfiguration;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\Utility\Events\EventBus;
use Unzer\Core\Infrastructure\Utility\GuidProvider;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\EncryptorMock;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\WebhookUrlServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\CurrencyServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\Events\TestEventEmitter;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestGuidProvider;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookData as WebhookDataEntity;
use Unzer\Core\BusinessLogic\Domain\Integration\Store\StoreService as IntegrationStoreService;

/**
 * Class BaseTestCase.
 *
 * @package Unzer\Core\Tests\BusinessLogic\Common
 */
class BaseTestCase extends TestCase
{
    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::getInstance();
        new TestServiceRegister([
            EventBus::class => function () {
                return TestEventEmitter::getInstance();
            },
            ShopLoggerAdapter::class => function () {
                return new TestShopLogger();
            },
            StoreContext::class => function () {
                return StoreContext::getInstance();
            },
            HttpClient::class => function () {
                return new TestHttpClient();
            },
            ConnectionSettingsRepositoryInterface::class => function () {
                return new ConnectionSettingsRepository(
                    TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },
            ConnectionService::class => static function () {
                return new ConnectionService(
                    (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test')),
                    TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    TestServiceRegister::getService(WebhookDataRepositoryInterface::class),
                    TestServiceRegister::getService(EncryptorInterface::class),
                    TestServiceRegister::getService(WebhookUrlServiceInterface::class)
                );
            },
            PaymentPageSettingsService::class => static function () {
                return new PaymentPageSettingsService(
                    TestServiceRegister::getService(PaymentPageSettingsRepositoryInterface::class),
                    TestServiceRegister::getService(UploaderService::class),
                );
            },
            WebhookDataRepositoryInterface::class => function () {
                return new WebhookDataRepository(
                    TestRepositoryRegistry::getRepository(WebhookDataEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },
            PaymentPageSettingsRepositoryInterface::class => function () {
                return new PaymentPageSettingsRepository(
                    TestRepositoryRegistry::getRepository(PaymentPageSettingsEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },
            DisconnectService::class => static function () {
                return new DisconnectService(
                    (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test')),
                    TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    TestServiceRegister::getService(WebhookDataRepositoryInterface::class),
                );
            },
            ConnectionController::class => function () {
                return new ConnectionController(
                    TestServiceRegister::getService(ConnectionService::class)
                );
            },
            DisconnectController::class => function () {
                return new DisconnectController(
                    TestServiceRegister::getService(DisconnectService::class)
                );
            },
            StoreService::class => function () {
                return new StoreService(
                    TestServiceRegister::getService(IntegrationStoreService::class)
                );
            },
            StoresController::class => function () {
                return new StoresController(
                    TestServiceRegister::getService(StoreService::class),
                    TestServiceRegister::getService(ConnectionService::class)
                );
            },
            CountryController::class => function () {
                return new CountryController(
                    TestServiceRegister::getService(CountryService::class)
                );
            },
            LanguageController::class => function () {
                return new LanguageController(
                    TestServiceRegister::getService(LanguageService::class)
                );
            },
            VersionController::class => function () {
                return new VersionController(
                    TestServiceRegister::getService(VersionService::class)
                );
            },
            PaymentPageSettingsController::class => function () {
                return new PaymentPageSettingsController(
                    TestServiceRegister::getService(PaymentPageSettingsService::class)
                );
            },
            PaymentMethodConfigRepositoryInterface::class => function () {
                return new PaymentMethodConfigRepository(
                    TestRepositoryRegistry::getRepository(PaymentMethodConfig::getClassName()),
                    StoreContext::getInstance()
                );
            },
            PaymentMethodService::class => static function () {
                return new PaymentMethodService(
                    (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test')),
                    TestServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class),
                    TestServiceRegister::getService(CurrencyServiceInterface::class),
                );
            },
            PaymentMethodsController::class => function () {
                return new PaymentMethodsController(
                    TestServiceRegister::getService(PaymentMethodService::class),
                    TestServiceRegister::getService(CurrencyServiceInterface::class)
                );
            },
            CheckoutPaymentMethodsController::class => function () {
                return new CheckoutPaymentMethodsController(
                    TestServiceRegister::getService(PaymentMethodService::class)
                );
            },
            PaymentPageService::class => static function () {
                return new PaymentPageService(
                    (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test')),
                    TestServiceRegister::getService(PaymentMethodService::class)
                );
            },
            CheckoutPaymentPageController::class => static function () {
                return new CheckoutPaymentPageController(TestServiceRegister::getService(PaymentPageService::class));
            }
        ]);

        TestServiceRegister::registerService(
            TimeProvider::class,
            function () {
                return TestTimeProvider::getInstance();
            }
        );

        TestServiceRegister::registerService(
            GuidProvider::CLASS_NAME,
            function () {
                return TestGuidProvider::getInstance();
            }
        );

        TestRepositoryRegistry::registerRepository(
            ConnectionSettingsEntity::getClassName(),
            MemoryRepositoryWithConditionalDelete::getClassName()
        );

        TestRepositoryRegistry::registerRepository(
            WebhookDataEntity::getClassName(),
            MemoryRepositoryWithConditionalDelete::getClassName()
        );

        TestRepositoryRegistry::registerRepository(
            PaymentPageSettingsEntity::getClassName(),
            MemoryRepositoryWithConditionalDelete::getClassName()
        );

        TestRepositoryRegistry::registerRepository(
            PaymentMethodConfig::getClassName(),
            MemoryRepositoryWithConditionalDelete::getClassName()
        );

        TestServiceRegister::registerService(
            EncryptorInterface::class,
            function () {
                return new EncryptorMock();
            }
        );

        TestServiceRegister::registerService(
            WebhookUrlServiceInterface::class,
            function () {
                return new WebhookUrlServiceMock();
            }
        );

        TestServiceRegister::registerService(
            CurrencyServiceInterface::class,
            function () {
                return new CurrencyServiceMock();
            }
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        TestRepositoryRegistry::cleanUp();
        MemoryStorage::reset();
        Logger::resetInstance();
        LoggerConfiguration::resetInstance();
    }
}
