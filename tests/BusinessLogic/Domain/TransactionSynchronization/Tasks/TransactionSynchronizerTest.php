<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\TransactionSynchronization\Tasks;

use Exception;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks\TransactionSynchronizer;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks\TransactionSyncTask;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use Unzer\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use Unzer\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use Unzer\Core\Infrastructure\Serializer\Serializer;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\TransactionHistoryServiceMock;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionSynchronizerTest.
 *
 * @package BusinessLogic\Domain\TransactionSynchronization\Tasks
 */
class TransactionSynchronizerTest extends BaseTestCase
{
    /**
     * @var QueueItemRepository
     */
    public QueueItemRepository $queueRepository;

    /**
     * @var TransactionHistoryServiceMock
     */
    private TransactionHistoryServiceMock $historyServiceMock;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueRepository = TestRepositoryRegistry::getQueueItemRepository();

        $this->historyServiceMock = new TransactionHistoryServiceMock(
            TestServiceRegister::getService(TransactionHistoryRepositoryInterface::class)
        );

        TestServiceRegister::registerService(TransactionHistoryService::class, function () {
            return $this->historyServiceMock;
        });
    }

    /**
     * @return void
     */
    public function testNativeSerialization(): void
    {
        // arrange
        TestServiceRegister::registerService(Serializer::CLASS_NAME, static function () {
            return new NativeSerializer();
        });

        $this->historyServiceMock->setPaymentIdsForSynchronization(['1', '2', '3']);
        $task = new TransactionSynchronizer('1');
        // act
        $serialized = Serializer::serialize($task);

        // assert
        self::assertEquals($task, Serializer::unserialize($serialized));
    }

    /**
     * @return void
     */
    public function testJsonSerialization(): void
    {
        // arrange
        $this->historyServiceMock->setPaymentIdsForSynchronization(['1', '2', '3']);
        $task = new TransactionSynchronizer('1');
        TestServiceRegister::registerService(Serializer::CLASS_NAME, static function () {
            return new JsonSerializer();
        });

        // act
        $serialized = Serializer::serialize($task);

        // assert
        self::assertEquals($task, Serializer::unserialize($serialized));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testOneSubTasksCreated(): void
    {
        // arrange
        $this->historyServiceMock->setPaymentIdsForSynchronization(['1', '2', '3']);
        $task = new TransactionSynchronizer('1');

        // act
        $task->execute();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertCount(1, $queueItems);
    }

    /**
     * @return void
     */
    public function testCorrectTaskCreated(): void
    {
        // arrange
        $this->historyServiceMock->setPaymentIdsForSynchronization(['1', '2', '3']);
        $task = new TransactionSynchronizer('1');


        // act
        $task->execute();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertInstanceOf(TransactionSyncTask::class, $queueItems[0]->getTask());
    }

    /**
     * @return void
     * @throws QueueStorageUnavailableException
     */
    public function testThreeSubTasksCreated(): void
    {
        $ids = [];

        for ($i = 0; $i < 250; $i++) {
            $ids[] = (string)$i;
        }

        // arrange
        $this->historyServiceMock->setPaymentIdsForSynchronization($ids);
        $task = new TransactionSynchronizer('1');

        // act
        $task->execute();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertCount(3, $queueItems);
    }
}
