<?php
/** @noinspection PhpDuplicateArrayKeysInspection */

namespace Unzer\Core\Tests\Infrastructure\Common;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Unzer\Core\Infrastructure\Configuration\ConfigEntity;
use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\Configuration\ConfigurationManager;
use Unzer\Core\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use Unzer\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Unzer\Core\Infrastructure\Logger\Logger;
use Unzer\Core\Infrastructure\Logger\LoggerConfiguration;
use Unzer\Core\Infrastructure\ORM\RepositoryRegistry;
use Unzer\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use Unzer\Core\Infrastructure\Serializer\Serializer;
use Unzer\Core\Infrastructure\Utility\Events\EventBus;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;

/**
 * Class BaseTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common
 */
abstract class BaseInfrastructureTestWithServices extends TestCase
{
    /**
     * @var TestShopConfiguration
     */
    public TestShopConfiguration $shopConfig;

    /**
     * @var TestShopLogger
     */
    public TestShopLogger $shopLogger;

    /**
     * @var TestTimeProvider
     */
    public TestTimeProvider $timeProvider;

    /**
     * @var TestDefaultLogger
     */
    public TestDefaultLogger $defaultLogger;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $me = $this;

        $this->timeProvider = new TestTimeProvider();
        $this->timeProvider->setCurrentLocalTime(new DateTime());
        $this->shopConfig = new TestShopConfiguration();
        $this->shopLogger = new TestShopLogger();
        $this->defaultLogger = new TestDefaultLogger();
        $this->serializer = new JsonSerializer();

        new TestServiceRegister(
            [
                ConfigurationManager::CLASS_NAME => function () {
                    return new TestConfigurationManager();
                },
                Configuration::CLASS_NAME => function () use ($me) {
                    return $me->shopConfig;
                },
                TimeProvider::CLASS_NAME => function () use ($me) {
                    return $me->timeProvider;
                },
                DefaultLoggerAdapter::CLASS_NAME => function () use ($me) {
                    return $me->defaultLogger;
                },
                ShopLoggerAdapter::CLASS_NAME => function () use ($me) {
                    return $me->shopLogger;
                },
                EventBus::CLASS_NAME => function () {
                    return EventBus::getInstance();
                },
                Serializer::CLASS_NAME => function () use ($me) {
                    return $me->serializer;
                },
            ]
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Logger::resetInstance();
        LoggerConfiguration::resetInstance();
        MemoryStorage::reset();
        TestRepositoryRegistry::cleanUp();

        parent::tearDown();
    }
}
