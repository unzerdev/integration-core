<?php

namespace BusinessLogic\Domain\TransactionSynchronization\Tasks;

use Exception;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Service\TransactionSynchronizerService;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Tasks\TransactionSyncTask;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use Unzer\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use Unzer\Core\Infrastructure\Serializer\Serializer;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\OrderServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\TransactionSynchronizerServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionSyncTaskTest.
 *
 * @package BusinessLogic\Domain\TransactionSynchronization\Tasks
 */
class TransactionSyncTaskTest extends BaseTestCase
{
    /**
     * @var TransactionSynchronizerServiceMock
     */
    public TransactionSynchronizerServiceMock $transactionSynchronizerServiceMock;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionSynchronizerServiceMock = new TransactionSynchronizerServiceMock(
            new UnzerFactoryMock(),
            TestServiceRegister::getService(TransactionHistoryService::class),
            new OrderServiceMock(),
            TestServiceRegister::getService(PaymentStatusMapService::class)
        );

        TestServiceRegister::registerService(
            TransactionSynchronizerService::class,
            function () {
                return $this->transactionSynchronizerServiceMock;
            }
        );
    }

    /**
     * @return void
     */
    public function testNativeSerialization(): void
    {
        // arrange
        $task = new TransactionSyncTask(['1', '2', '3']);
        TestServiceRegister::registerService(Serializer::CLASS_NAME, static function () {
            return new NativeSerializer();
        });

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
        $task = new TransactionSyncTask(['1', '2', '3']);
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
     *
     * @throws Exception
     */
    public function testSendingStatisticsReportOnly(): void
    {
        // arrange
        $task = new TransactionSyncTask(['1', '2', '3']);

        // act
        $task->execute();

        // assert
        $methodCallHistory = $this->transactionSynchronizerServiceMock->getCallHistory('synchronizeTransactions');
        self::assertNotEmpty($methodCallHistory);
        self::assertCount(3, $methodCallHistory);
        self::assertEquals('1', $methodCallHistory[0]['orderId']);
        self::assertEquals('2', $methodCallHistory[1]['orderId']);
        self::assertEquals('3', $methodCallHistory[2]['orderId']);
    }
}
