<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\TransactionSynchronization\Listeners;

use DateInterval;
use Exception;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Listeners\TransactionSyncListener;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks\TransactionSynchronizer;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Unzer\Core\Infrastructure\TaskExecution\QueueItem;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\ConnectionServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionSyncListenerTest.
 *
 * @package BusinessLogic\Domain\TransactionSynchronization\Listeners
 */
class TransactionSyncListenerTest extends BaseTestCase
{
    /**
     * @var TransactionSyncListener
     */
    private TransactionSyncListener $listener;

    /**
     * @var QueueItemRepository
     */
    public QueueItemRepository $queueRepository;

    /**
     * @var TestTimeProvider
     */
    private TestTimeProvider $timeProvider;

    /**
     * @var ConnectionServiceMock $connectionServiceMock
     */
    private ConnectionServiceMock $connectionServiceMock;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->timeProvider = new TestTimeProvider();
        $this->listener = new TransactionSyncListener();
        $this->queueRepository = TestRepositoryRegistry::getQueueItemRepository();
        TestServiceRegister::registerService(
            TimeProvider::class,
            function () {
                return $this->timeProvider;
            }
        );

        $this->connectionServiceMock = new ConnectionServiceMock(
            new UnzerFactoryMock(),
            TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
            TestServiceRegister::getService(WebhookDataRepositoryInterface::class),
            TestServiceRegister::getService(EncryptorInterface::class),
            TestServiceRegister::getService(WebhookUrlServiceInterface::class)
        );

        TestServiceRegister::registerService(
            ConnectionService::class, function () {
            return $this->connectionServiceMock;
        });
    }

    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     */
    public function testTaskEnqueuedNoTaskInDatabase(): void
    {
        // arrange
        $this->connectionServiceMock->setIds(['1', '2']);

        // act
        $this->listener->handle();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertNotEmpty($queueItems);
        self::assertCount(2, $queueItems);
    }

    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     *
     * @throws Exception
     */
    public function testTaskNotEnqueued(): void
    {
        // arrange
        $this->connectionServiceMock->setIds(['1', '2']);
        $task = new TransactionSynchronizer('1');
        $queueItem = new QueueItem($task);

        $time = $this->timeProvider->getCurrentLocalTime();
        $queueItem->setQueueTimestamp($time->sub(new DateInterval('PT6H'))->getTimestamp());
        $this->queueRepository->save($queueItem);

        // act
        $this->listener->handle();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertNotEmpty($queueItems);
        self::assertCount(1, $queueItems);
    }

    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     *
     * @throws Exception
     */
    public function testTaskNotEnqueuedNoConnectedStores(): void
    {
        // arrange
        $this->connectionServiceMock->setIds([]);
        $task = new TransactionSynchronizer('1');
        $queueItem = new QueueItem($task);

        $time = $this->timeProvider->getCurrentLocalTime();
        $queueItem->setQueueTimestamp($time->getTimestamp());
        $this->queueRepository->save($queueItem);

        // act
        $this->listener->handle();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertNotEmpty($queueItems);
        self::assertCount(1, $queueItems);
    }

    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     *
     * @throws Exception
     */
    public function testOneTaskEnqueued(): void
    {
        // arrange
        $this->connectionServiceMock->setIds(['1']);
        $task = new TransactionSynchronizer('1');
        $queueItem = new QueueItem($task);

        $time = $this->timeProvider->getCurrentLocalTime();
        $queueItem->setQueueTimestamp($time->sub(new DateInterval('P2D'))->getTimestamp());
        $this->queueRepository->save($queueItem);

        // act
        $this->listener->handle();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertNotEmpty($queueItems);
        self::assertCount(2, $queueItems);
    }
}
