<?php

namespace Unzer\Core\Tests\BusinessLogic\Common;

use PHPUnit\Framework\TestCase;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Controller\ConnectionController;
use Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Controller\DisconnectController;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Repositories\WebhookDataRepository;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
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
                    TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    TestServiceRegister::getService(WebhookDataRepositoryInterface::class),
                    TestServiceRegister::getService(EncryptorInterface::class),
                    TestServiceRegister::getService(WebhookUrlServiceInterface::class)
                );
            },
            WebhookDataRepositoryInterface::class => function () {
                return new WebhookDataRepository(
                    TestRepositoryRegistry::getRepository(WebhookDataEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },
            DisconnectService::class => static function () {
                return new DisconnectService(
                    new UnzerMock('s-priv-test'),
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
