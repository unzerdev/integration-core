<?php
/** @noinspection PhpDuplicateArrayKeysInspection */

namespace Unzer\Core\Tests\Infrastructure\TaskExecution;

use Unzer\Core\Infrastructure\Configuration\ConfigEntity;
use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\Configuration\ConfigurationManager;
use Unzer\Core\Infrastructure\Http\HttpClient;
use Unzer\Core\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use Unzer\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Unzer\Core\Infrastructure\Logger\Logger;
use Unzer\Core\Infrastructure\ORM\RepositoryRegistry;
use Unzer\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use Unzer\Core\Infrastructure\Serializer\Serializer;
use Unzer\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Unzer\Core\Infrastructure\TaskExecution\QueueItem;
use Unzer\Core\Infrastructure\TaskExecution\QueueItemStarter;
use Unzer\Core\Infrastructure\TaskExecution\QueueService;
use Unzer\Core\Infrastructure\Utility\Events\EventBus;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueItemStarterTest
 *
 * @package Unzer\Core\Tests\Infrastructure\TaskExecution
 */
class QueueItemStarterTest extends TestCase
{
    /** @var TestQueueService */
    public $queue;
    /** @var MemoryQueueItemRepository */
    public $queueStorage;
    /** @var TestTimeProvider */
    public $timeProvider;
    /** @var TestShopLogger */
    public $logger;
    /** @var Configuration */
    public $shopConfiguration;
    /** @var ConfigurationManager */
    public $configurationManager;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        RepositoryRegistry::registerRepository(QueueItem::CLASS_NAME, MemoryQueueItemRepository::getClassName());
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $timeProvider = new TestTimeProvider();
        $queue = new TestQueueService();
        $shopLogger = new TestShopLogger();
        $configurationManager = new TestConfigurationManager();
        $shopConfiguration = new TestShopConfiguration();
        $serializer = new JsonSerializer();

        new TestServiceRegister(
            array(
                ConfigurationManager::CLASS_NAME => function () use ($configurationManager) {
                    return $configurationManager;
                },
                TimeProvider::CLASS_NAME => function () use ($timeProvider) {
                    return $timeProvider;
                },
                TaskRunnerWakeup::CLASS_NAME => function () {
                    return new TestTaskRunnerWakeupService();
                },
                QueueService::CLASS_NAME => function () use ($queue) {
                    return $queue;
                },
                EventBus::CLASS_NAME => function () {
                    return EventBus::getInstance();
                },
                DefaultLoggerAdapter::CLASS_NAME => function () {
                    return new TestDefaultLogger();
                },
                ShopLoggerAdapter::CLASS_NAME => function () use ($shopLogger) {
                    return $shopLogger;
                },
                Configuration::CLASS_NAME => function () use ($shopConfiguration) {
                    return $shopConfiguration;
                },
                HttpClient::CLASS_NAME => function () {
                    return new TestHttpClient();
                },
                Serializer::CLASS_NAME => function () use ($serializer) {
                    return $serializer;
                },
                QueueItemStateTransitionEventBus::CLASS_NAME => function () {
                    return QueueItemStateTransitionEventBus::getInstance();
                },
            )
        );


        // Initialize logger component with new set of log adapters
        Logger::resetInstance();

        $shopConfiguration->setIntegrationName('Shop1');

        $this->queueStorage = RepositoryRegistry::getQueueItemRepository();
        $this->timeProvider = $timeProvider;
        $this->queue = $queue;
        $this->logger = $shopLogger;
        $this->shopConfiguration = $shopConfiguration;
        $this->configurationManager = $configurationManager;
    }

    /**
     * @throws QueueStorageUnavailableException
     */
    public function testRunningItemStarter()
    {
        // Arrange
        $queueItem = $this->queue->enqueue(
            'test',
            new FooTask()
        );
        $itemStarter = new QueueItemStarter($queueItem->getId());

        // Act
        $itemStarter->run();

        // Assert
        $findCallHistory = $this->queue->getMethodCallHistory('find');
        $startCallHistory = $this->queue->getMethodCallHistory('start');
        self::assertCount(1, $findCallHistory);
        self::assertCount(1, $startCallHistory);
        self::assertEquals($queueItem->getId(), $findCallHistory[0]['id']);
        /** @var QueueItem $startedQueueItem */
        $startedQueueItem = $startCallHistory[0]['queueItem'];
        self::assertEquals($queueItem->getId(), $startedQueueItem->getId());
    }

    /**
     * @throws QueueStorageUnavailableException
     */
    public function testItemStarterMustBeRunnableAfterDeserialization()
    {
        // Arrange
        $queueItem = $this->queue->enqueue(
            'test',
            new FooTask()
        );
        $itemStarter = new QueueItemStarter($queueItem->getId());
        /** @var QueueItemStarter $unserializedItemStarter */
        $unserializedItemStarter = Serializer::unserialize(Serializer::serialize($itemStarter));

        // Act
        $unserializedItemStarter->run();

        // Assert
        $findCallHistory = $this->queue->getMethodCallHistory('find');
        $startCallHistory = $this->queue->getMethodCallHistory('start');
        self::assertCount(1, $findCallHistory);
        self::assertCount(1, $startCallHistory);
        self::assertEquals($queueItem->getId(), $findCallHistory[0]['id']);
        /** @var QueueItem $startedQueueItem */
        $startedQueueItem = $startCallHistory[0]['queueItem'];
        self::assertEquals($queueItem->getId(), $startedQueueItem->getId());
    }

    /**
     * @throws QueueStorageUnavailableException
     */
    public function testItemsStarterMustSetTaskExecutionContextInConfiguration()
    {
        // Arrange
        $queueItem = $this->queue->enqueue('test', new FooTask(), 'test');
        $itemStarter = new QueueItemStarter($queueItem->getId());

        // Act
        $itemStarter->run();

        // Assert
        self::assertSame(
            'test',
            $this->configurationManager->getContext(),
            'Item starter must set task context before task execution.'
        );
    }
}
