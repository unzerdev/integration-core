<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\OrderManagement\Services;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Services\OrderManagementService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\AuthorizeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\ChargeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\TransactionHistoryServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderManagementServiceTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\Domain\OrderManagement\Services
 */
class OrderManagementServiceTest extends BaseTestCase
{
    /** @var OrderManagementService */
    private OrderManagementService $orderManagementService;

    /** @var TransactionHistoryService */
    private TransactionHistoryService $transactionHistoryService;

    /** @var UnzerFactoryMock|null */
    private UnzerFactoryMock $unzerFactory;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->unzerFactory = (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test'));
        $this->transactionHistoryService = new TransactionHistoryServiceMock(
            TestServiceRegister::getService(TransactionHistoryRepositoryInterface::class)
        );
        TestServiceRegister::registerService(
            TransactionHistoryService::class,
            function () {
                return $this->transactionHistoryService;
            }
        );
        $this->orderManagementService = new OrderManagementService(
            $this->unzerFactory,
            $this->transactionHistoryService
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeTransactionHistoryNotFound(): void
    {
        // arrange

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );
        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('chargeAuthorization');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationTransactionHistoryNotFound(): void
    {
        // arrange
        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'cancelOrder'],
            ['orderId', Amount::fromFloat(1.1, Currency::getDefault())]
        );
        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizationByPayment');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundOrderTransactionHistoryNotFound(): void
    {
        // arrange
        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'refundOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );
        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeTransactionHistoryInvalidNoRemainingAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('chargeAuthorization');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationTransactionHistoryInvalidNoRemainingAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'cancelOrder'], [
            'orderId',
            Amount::fromFloat
            (
                1.1,
                Currency::getDefault()
            )
        ]);

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizationByPayment');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundTransactionHistoryInvalidNoRemainingAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'refundOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeTransactionHistoryInvalidNoCancelledAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            null,
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('chargeAuthorization');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationTransactionHistoryInvalidNoCancelledAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            null,
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'cancelOrder'], [
            'orderId',
            Amount::fromFloat
            (
                1.1,
                Currency::getDefault()
            )
        ]);

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizationByPayment');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundTransactionHistoryInvalidNoCancelledAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            null,
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'refundOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeTransactionHistoryInvalidNoChargedAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('chargeAuthorization');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationTransactionHistoryInvalidNoChargedAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'cancelOrder'],
            ['orderId', Amount::fromFloat(1.1, Currency::getDefault())]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizationByPayment');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundTransactionHistoryInvalidNoChargedAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            null,
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'refundOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizationByPayment');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeTransactionHistoryInvalidNoTotalAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('chargeAuthorization');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationTransactionHistoryInvalidNoTotalAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'cancelOrder'],
            ['orderId', Amount::fromFloat(1.1, Currency::getDefault())]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizationByPayment');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundTransactionHistoryInvalidNoTotalAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            null,
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault()),
            Amount::fromFloat(1.1, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'refundOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeNotPossibleNoRemainingAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(50, Currency::getDefault()),
            Amount::fromFloat(50, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('chargeAuthorization');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeNotPossibleAmountGreaterThanPossibleAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(40, Currency::getDefault()),
            Amount::fromFloat(50, Currency::getDefault()),
            Amount::fromFloat(10, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(30, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('chargeAuthorization');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCharge(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(3, 'complete'),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(40, Currency::getDefault()),
            Amount::fromFloat(50, Currency::getDefault()),
            Amount::fromFloat(10, Currency::getDefault())
        );

        $authorizedItem = new AuthorizeHistoryItem(
            'paymentId', '11.11.2011.', Amount::fromFloat(100, Currency::getDefault()),
        'authorized', Amount::fromFloat(0, Currency::getDefault()), 'card', 'paymentId');
        $transactionHistory->collection()->add($authorizedItem);
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(10, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('chargeAuthorization');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('paymentId', $methodCallHistory[0]['payment']);
        self::assertEquals(10.0, $methodCallHistory[0]['amount']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationNotPossibleNoRemainingAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(50, Currency::getDefault()),
            Amount::fromFloat(50, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'cancelOrder'],
            ['orderId', Amount::fromFloat(1.1, Currency::getDefault())]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizationByPayment');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationNotPossibleAmountGreaterThanRemaining(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(40, Currency::getDefault()),
            Amount::fromFloat(50, Currency::getDefault()),
            Amount::fromFloat(10, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'cancelOrder'],
            ['orderId', Amount::fromFloat(12, Currency::getDefault())]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizationByPayment');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellation(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(3, 'complete'),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(100, Currency::getDefault())
        );

        $authorizedItem = new AuthorizeHistoryItem(
            'paymentId', '11.11.2011.', Amount::fromFloat(100, Currency::getDefault()),
            'authorized', Amount::fromFloat(0, Currency::getDefault()), 'card', 'paymentId');
        $transactionHistory->collection()->add($authorizedItem);

        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'cancelOrder'],
            ['orderId', Amount::fromFloat(1.1, Currency::getDefault())]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizationByPayment');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('paymentId', $methodCallHistory[0]['payment']);
        self::assertEquals(1.1, $methodCallHistory[0]['amount']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundNotPossible(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(3, 'complete'),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(40, Currency::getDefault()),
            Amount::fromFloat(20, Currency::getDefault()),
            Amount::fromFloat(10, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'refundOrder'],
            [
                'orderId',
                Amount::fromFloat(30, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefund(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(3, 'complete'),
            Amount::fromFloat(1000, Currency::getDefault()),
            Amount::fromFloat(980, Currency::getDefault()),
            Amount::fromFloat(20, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            [
                new ChargeHistoryItem(
                    'charge1', 'date1', Amount::fromFloat(100, Currency::getDefault()), 'status1',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge2', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge3', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge4', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
            ]
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'refundOrder'],
            [
                'orderId',
                Amount::fromFloat(30, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('id', $methodCallHistory[0]['payment']);
        self::assertEquals('charge1', $methodCallHistory[0]['chargeId']);
        self::assertEquals(30, $methodCallHistory[0]['amount']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundingMultipleChargeItems(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(3, 'complete'),
            Amount::fromFloat(1000, Currency::getDefault()),
            Amount::fromFloat(980, Currency::getDefault()),
            Amount::fromFloat(20, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            [
                new ChargeHistoryItem(
                    'charge1', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge2', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                    Amount::fromFloat(5, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge3', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                    Amount::fromFloat(5, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge4', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                    Amount::fromFloat(3, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge5', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
            ]
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'refundOrder'],
            [
                'orderId',
                Amount::fromFloat(30, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertCount(5, $methodCallHistory);
        self::assertEquals('id', $methodCallHistory[0]['payment']);
        self::assertEquals('charge1', $methodCallHistory[0]['chargeId']);
        self::assertEquals(10, $methodCallHistory[0]['amount']);
        self::assertEquals('id', $methodCallHistory[1]['payment']);
        self::assertEquals('charge2', $methodCallHistory[1]['chargeId']);
        self::assertEquals(5, $methodCallHistory[1]['amount']);
        self::assertEquals('id', $methodCallHistory[2]['payment']);
        self::assertEquals('charge3', $methodCallHistory[2]['chargeId']);
        self::assertEquals(5, $methodCallHistory[2]['amount']);
        self::assertEquals('id', $methodCallHistory[3]['payment']);
        self::assertEquals('charge4', $methodCallHistory[3]['chargeId']);
        self::assertEquals(7, $methodCallHistory[3]['amount']);
        self::assertEquals('id', $methodCallHistory[4]['payment']);
        self::assertEquals('charge5', $methodCallHistory[4]['chargeId']);
        self::assertEquals(3, $methodCallHistory[4]['amount']);
    }
}

