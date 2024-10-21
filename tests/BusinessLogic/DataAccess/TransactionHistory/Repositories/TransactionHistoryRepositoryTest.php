<?php

namespace Unzer\Core\Tests\BusinessLogic\TransactionHistory\Repositories;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory as TransactionHistoryEntity;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
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
        $this->transactionHistoryRepository = TestServiceRegister::getService(TransactionHistoryRepositoryInterface::class);
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
    public function testTransactionHistory(): void
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
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
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
    public function testGetPaymentConfigDifferentStore(): void
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
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
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
    public function testGetPaymentConfigDifferentOrderId(): void
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
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
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
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null
        );

        // act
        StoreContext::doWithStore('1',
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
        $configEntity->setOrderId('order1');
        $configEntity->setStoreId('1');
        $this->repository->save($configEntity);

        $newTransactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(2, 'paid2'),
            Amount::fromFloat(12.11, Currency::getDefault()),
            Amount::fromFloat(12.11, Currency::getDefault()),
            Amount::fromFloat(11.11, Currency::getDefault()),
            null
        );

        // act
        StoreContext::doWithStore('1',
            [$this->transactionHistoryRepository, 'setTransactionHistory'],
            [$newTransactionHistory]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($newTransactionHistory, $savedEntity[0]->getTransactionHistory());
    }
}
