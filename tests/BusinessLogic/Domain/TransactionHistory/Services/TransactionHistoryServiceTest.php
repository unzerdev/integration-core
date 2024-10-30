<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\TransactionHistory\Services;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory as TransactionHistoryEntity;
use Unzer\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\Utility\TimeProvider;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionHistoryServiceTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\Domain\TransactionHistory\Services
 */
class TransactionHistoryServiceTest extends BaseTestCase
{
    /**
     * @var TransactionHistoryService
     */
    public $service;

    /**
     * @var MemoryRepository
     */
    public $repository;

    /**
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(TransactionHistoryEntity::getClassName());
        $this->service = TestServiceRegister::getService(TransactionHistoryService::class);
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws Exception
     */
    public function testSaveConnectionData(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );

        // act
        StoreContext::doWithStore('1', [$this->service, 'saveTransactionHistory'], [$transactionHistory]);

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($transactionHistory, $savedEntity->getTransactionHistory());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetTransactionHistoryNoTransactionHistory(): void
    {
        // arrange

        // act
        $transactionHistory = StoreContext::doWithStore('1', [$this->service, 'getTransactionHistoryByOrderId'], ['1']);

        // assert
        self::assertNull($transactionHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetTransactionHistory(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );
        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory);
        $configEntity->setOrderId('1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($configEntity);

        // act
        $fetchedTransactionHistory = StoreContext::doWithStore(
            '1',
            [$this->service, 'getTransactionHistoryByOrderId'],
            ['1']
        );

        // assert
        self::assertEquals($transactionHistory, $fetchedTransactionHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetTransactionHistoryForSynchronizationNoHistories(): void
    {
        // arrange

        // act
        $transactionHistories = StoreContext::doWithStore('1', [$this->service, 'getOrderIdsForSynchronization']);

        // assert

        self::assertEmpty($transactionHistories);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetTransactionHistoryForSynchronization(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );
        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory);
        $configEntity->setOrderId('1');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($configEntity);

        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order2',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );
        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory);
        $configEntity->setOrderId('2');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp());
        $this->repository->save($configEntity);


        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order3',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );
        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory);
        $configEntity->setOrderId('3');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(1);
        $this->repository->save($configEntity);


        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order4',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );
        $configEntity = new TransactionHistoryEntity();
        $configEntity->setTransactionHistory($transactionHistory);
        $configEntity->setOrderId('4');
        $configEntity->setStoreId('1');
        $configEntity->setUpdatedAt(2);
        $this->repository->save($configEntity);

        // act
        $transactionHistories = StoreContext::doWithStore('1', [$this->service, 'getOrderIdsForSynchronization']);

        // assert
        self::assertNotEmpty($transactionHistories);
        self::assertCount(2, $transactionHistories);
        self::assertEquals(['order1', 'order2'], $transactionHistories);
    }
}


