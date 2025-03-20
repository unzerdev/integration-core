<?php

namespace Unzer\Core\Tests\BusinessLogic\TransactionHistory\Repositories;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory as TransactionHistoryEntity;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\AuthorizeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\ChargeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;

/**
 * Class TransactionHistoryRepositoryTest.
 *
 * @package BusinessLogic\DataAccess\TransactionHistory\Repositories
 */
class TransactionHistoryRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private RepositoryInterface $repository;

    /** @var TransactionHistoryRepositoryInterface */
    private $transactionHistoryRepository;

    /**
     * @throws RepositoryNotRegisteredException
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(TransactionHistoryEntity::getClassName());
        $this->transactionHistoryRepository = TestServiceRegister::getService(
            TransactionHistoryRepositoryInterface::class
        );
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionHistoryNoHistory(): void
    {
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->transactionHistoryRepository, 'getTransactionHistoryByOrderId'], ['1']
        );

        // assert
        self::assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionHistory(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            [
                new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1',
                    PaymentMethodTypes::APPLE_PAY, '1'),
                new AuthorizeHistoryItem('id2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1'),
                new ChargeHistoryItem('id3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1')
            ]
        );
        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory);
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($configEntity);

        // act
        $fetchedTransaction = StoreContext::doWithStore(
            '1',
            [$this->transactionHistoryRepository, 'getTransactionHistoryByOrderId'],
            ['order1']
        );

        // assert
        self::assertEquals($transactionHistory, $fetchedTransaction);
    }

    /**
     * @throws Exception
     */
    public function testGetHistoryDifferentStore(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            [
                new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1',
                    PaymentMethodTypes::APPLE_PAY, '1'),
                new AuthorizeHistoryItem('id2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1'),
                new ChargeHistoryItem('id3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1')
            ]
        );
        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory);
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($configEntity);

        // act
        $fetchedTransaction = StoreContext::doWithStore(
            '2',
            [$this->transactionHistoryRepository, 'getTransactionHistoryByOrderId'],
            ['order1']
        );

        // assert
        self::assertNull($fetchedTransaction);
    }

    /**
     * @throws Exception
     */
    public function testGetHistoryDifferentOrderId(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            [
                new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1',
                    PaymentMethodTypes::APPLE_PAY, '1'),
                new AuthorizeHistoryItem('id2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1'),
                new ChargeHistoryItem('id3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1')
            ]
        );
        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory);
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($configEntity);

        // act
        $fetchedTransaction = StoreContext::doWithStore(
            '1',
            [$this->transactionHistoryRepository, 'getTransactionHistoryByOrderId'],
            ['order2']
        );

        // assert
        self::assertNull($fetchedTransaction);
    }

    /**
     * @throws Exception
     */
    public function testSaveConfigNoConfig(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            [
                new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1',
                    PaymentMethodTypes::APPLE_PAY, '1'),
                new AuthorizeHistoryItem('id2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1'),
                new ChargeHistoryItem('id3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1')
            ]
        );

        // act
        StoreContext::doWithStore(
            '1',
            [$this->transactionHistoryRepository, 'setTransactionHistory'],
            [$transactionHistory]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($transactionHistory, $savedEntity[0]->getTransactionHistory());
    }

    /**
     * @throws Exception
     */
    public function testSaveConfigUpdate(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            [
                new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1',
                    PaymentMethodTypes::APPLE_PAY, '1'),
                new AuthorizeHistoryItem('id2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1'),
                new ChargeHistoryItem('id3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3',
                    Amount::fromFloat(1, Currency::getDefault()), PaymentMethodTypes::APPLE_PAY, '1')
            ]
        );

        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory);
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($configEntity);

        $newTransactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(2, 'paid2'),
            Amount::fromFloat(12.11, Currency::getDefault()),
            Amount::fromFloat(12.11, Currency::getDefault()),
            Amount::fromFloat(11.11, Currency::getDefault()),
            null
        );

        // act
        StoreContext::doWithStore(
            '1',
            [$this->transactionHistoryRepository, 'setTransactionHistory'],
            [$newTransactionHistory]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($newTransactionHistory, $savedEntity[0]->getTransactionHistory());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteTransactionHistoryEntities(): void
    {
        $transactionHistory1 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );

        $transactionHistory2 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );

        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory1);
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($configEntity);

        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory2);
        $configEntity->setOrderId('order2');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($configEntity);

        // act
        StoreContext::doWithStore('1', [$this->transactionHistoryRepository, 'deleteTransactionHistoryEntities']);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(0, $savedEntity);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetTransactionByUpdateTimeNoEntities(): void
    {
        // arrange
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->transactionHistoryRepository, 'getTransactionHistoriesByUpdateTime'], [123]
        );

        // assert
        self::assertEmpty($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetTransactionByUpdateTime(): void
    {
        // arrange
        $transactionHistory1 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );

        $transactionHistory2 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );

        $transactionHistory3 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );

        $transactionHistory4 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'order1',
            'EUR',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );

        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory1);
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(124);
        $this->repository->save($configEntity);

        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory2);
        $configEntity->setOrderId('order2');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(111);
        $this->repository->save($configEntity);

        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory3);
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(4432432);
        $this->repository->save($configEntity);

        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory4);
        $configEntity->setOrderId('order2');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(12);
        $this->repository->save($configEntity);

        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->transactionHistoryRepository, 'getTransactionHistoriesByUpdateTime'], [123]
        );

        // assert
        self::assertNotEmpty($result);
        self::assertCount(2, $result);
    }
}
