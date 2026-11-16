<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\OrderManagement\Services;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\ChargePaymentNotReadableException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\CancelResult;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\CancelSkipReason;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\ChargeResult;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\ChargeSkipReason;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\RefundResult;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\RefundSkipReason;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Services\OrderManagementService;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\AuthorizeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\ChargeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\TransactionHistoryServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Constants\PaymentState as UnzerPaymentState;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\EmbeddedResources\CompanyInfo;
use UnzerSDK\Resources\EmbeddedResources\CompanyOwner;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\TransactionTypes\Cancellation;

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
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault()),
                'test-reference'
            ]
        );
        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertEmpty($methodCallHistory);
        self::assertEquals(ChargeResult::SKIPPED, $result->getStatus());
        self::assertEquals(ChargeSkipReason::PAYMENT_NOT_FOUND, $result->getSkipReason());
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
            ['orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'test-reference']
        );
        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
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
                Amount::fromFloat(1.1, Currency::getDefault()),
                'test-reference'
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
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertEmpty($methodCallHistory);
        self::assertEquals(ChargeResult::SKIPPED, $result->getStatus());
        self::assertEquals(ChargeSkipReason::INVALID_HISTORY, $result->getSkipReason());
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
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
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
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertEmpty($methodCallHistory);
        self::assertEquals(ChargeResult::SKIPPED, $result->getStatus());
        self::assertEquals(ChargeSkipReason::INVALID_HISTORY, $result->getSkipReason());
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
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
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
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertEmpty($methodCallHistory);
        self::assertEquals(ChargeResult::SKIPPED, $result->getStatus());
        self::assertEquals(ChargeSkipReason::INVALID_HISTORY, $result->getSkipReason());
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
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
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
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
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
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertEmpty($methodCallHistory);
        self::assertEquals(ChargeResult::SKIPPED, $result->getStatus());
        self::assertEquals(ChargeSkipReason::INVALID_HISTORY, $result->getSkipReason());
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
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
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
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertEmpty($methodCallHistory);
        self::assertEquals(ChargeResult::SKIPPED, $result->getStatus());
        self::assertEquals(ChargeSkipReason::INVALID_HISTORY, $result->getSkipReason());
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
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(30, Currency::getDefault())
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertEmpty($methodCallHistory);
        self::assertEquals(ChargeResult::SKIPPED, $result->getStatus());
        self::assertEquals(ChargeSkipReason::INVALID_HISTORY, $result->getSkipReason());
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
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
                'orderId',
                Amount::fromFloat(10, Currency::getDefault()),
                'charge-ref-123'
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('paymentId', $methodCallHistory[0]['payment']);
        /** @var \UnzerSDK\Resources\TransactionTypes\Charge $charge */
        $charge = $methodCallHistory[0]['charge'];
        self::assertEquals(10.0, $charge->getAmount());
        self::assertEquals('charge-ref-123', $charge->getPaymentReference());
        self::assertTrue($result->isCharged());
        self::assertSame($methodCallHistory[0]['charge'], $result->getCharge());
        self::assertEquals('orderId', $result->getTransactionHistory()->getOrderId());
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
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
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
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationAmountNullUsesTotalAmount(): void
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
            'paymentId',
            '11.11.2011.',
            Amount::fromFloat(100, Currency::getDefault()),
            'authorized',
            Amount::fromFloat(0, Currency::getDefault()),
            'card',
            'paymentId'
        );

        $transactionHistory->collection()->add($authorizedItem);
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'cancelOrder'], ['orderId', null, null]);

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('paymentId', $methodCallHistory[0]['payment']);
        /** @var Cancellation $cancellation */
        $cancellation = $methodCallHistory[0]['cancellation'];
        self::assertEquals(100.0, $cancellation->getAmount());
    }

    public function testCancellationAmountNullNoAuthorizedItem(): void
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
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'cancelOrder'], ['orderId', null]);

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
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
            ['orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'cancel-ref-123']
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('paymentId', $methodCallHistory[0]['payment']);
        /** @var Cancellation $cancellation */
        $cancellation = $methodCallHistory[0]['cancellation'];
        self::assertEquals(1.1, $cancellation->getAmount());
        self::assertEquals('cancel-ref-123', $cancellation->getPaymentReference());
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
                    'charge1', 'date1', Amount::fromFloat(100, Currency::getDefault()), 'Success',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge2', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'Success',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge3', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'Success',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge4', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'Success',
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
                Amount::fromFloat(30, Currency::getDefault()),
                'refund-ref-123'
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('id', $methodCallHistory[0]['payment']);
        self::assertEquals('charge1', $methodCallHistory[0]['chargeId']);
        self::assertEquals(30, $methodCallHistory[0]['amount']);
        self::assertEquals('refund-ref-123', $methodCallHistory[0]['referenceText']);
    }

    /**
     * @throws Exception
     */
    public function testRefundByPayment(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'paylater-installment',
            'orderId',
            'EUR',
            new PaymentState(3, 'complete'),
            Amount::fromFloat(1000, Currency::getDefault()),
            Amount::fromFloat(980,  Currency::getDefault()),
            Amount::fromFloat(20,   Currency::getDefault()),
            Amount::fromFloat(0,    Currency::getDefault()),
            [
                new AuthorizeHistoryItem(
                    'authorize1', 'date1', Amount::fromFloat(100, Currency::getDefault()),
                    'status1', Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                // A refund is decided on the successful charges (what is still refundable on them).
                new ChargeHistoryItem(
                    'charge1', 'date2', Amount::fromFloat(100, Currency::getDefault()), 'success',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                )
            ]
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'refundOrder'],
            [
                'orderId',
                Amount::fromFloat(30, Currency::getDefault()),
                'payment-ref-456'
            ]
        );

        // assert
        $paymentCalls = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargedPayment');
        self::assertNotEmpty($paymentCalls);
        self::assertCount(1, $paymentCalls);

        self::assertEquals('id', $paymentCalls[0]['payment']);
        $cnl = $paymentCalls[0]['cancellation'];
        self::assertInstanceOf(Cancellation::class, $cnl);
        self::assertEquals(30.0, $cnl->getAmount());
        self::assertEquals('payment-ref-456', $cnl->getPaymentReference());
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
                    'charge1', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'Success',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge2', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'Success',
                    Amount::fromFloat(5, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge3', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'Success',
                    Amount::fromFloat(5, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge4', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'Success',
                    Amount::fromFloat(3, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge5', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'Success',
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
                Amount::fromFloat(30, Currency::getDefault()),
                'multi-ref'
            ]
        );

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertCount(5, $methodCallHistory);
        self::assertEquals('id', $methodCallHistory[0]['payment']);
        self::assertEquals('charge1', $methodCallHistory[0]['chargeId']);
        self::assertEquals(10, $methodCallHistory[0]['amount']);
        self::assertEquals('multi-ref', $methodCallHistory[0]['referenceText']);
        self::assertEquals('id', $methodCallHistory[1]['payment']);
        self::assertEquals('charge2', $methodCallHistory[1]['chargeId']);
        self::assertEquals(5, $methodCallHistory[1]['amount']);
        self::assertEquals('multi-ref', $methodCallHistory[1]['referenceText']);
        self::assertEquals('id', $methodCallHistory[2]['payment']);
        self::assertEquals('charge3', $methodCallHistory[2]['chargeId']);
        self::assertEquals(5, $methodCallHistory[2]['amount']);
        self::assertEquals('multi-ref', $methodCallHistory[2]['referenceText']);
        self::assertEquals('id', $methodCallHistory[3]['payment']);
        self::assertEquals('charge4', $methodCallHistory[3]['chargeId']);
        self::assertEquals(7, $methodCallHistory[3]['amount']);
        self::assertEquals('multi-ref', $methodCallHistory[3]['referenceText']);
        self::assertEquals('id', $methodCallHistory[4]['payment']);
        self::assertEquals('charge5', $methodCallHistory[4]['chargeId']);
        self::assertEquals(3, $methodCallHistory[4]['amount']);
        self::assertEquals('multi-ref', $methodCallHistory[4]['referenceText']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundSkipsAlreadyCanceledCharges(): void
    {
        // arrange - charge1 is fully canceled (cancelledAmount == chargeAmount)
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(3, 'complete'),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(50, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            [
                new ChargeHistoryItem(
                    'charge1', 'date1', Amount::fromFloat(50, Currency::getDefault()), 'Success',
                    Amount::fromFloat(50, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge2', 'date1', Amount::fromFloat(50, Currency::getDefault()), 'Success',
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
                Amount::fromFloat(30, Currency::getDefault()),
                'ref-text'
            ]
        );

        // assert - only charge2 should be refunded, charge1 should be skipped
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertCount(1, $methodCallHistory);
        self::assertEquals('charge2', $methodCallHistory[0]['chargeId']);
        self::assertEquals(30, $methodCallHistory[0]['amount']);
        self::assertEquals('ref-text', $methodCallHistory[0]['referenceText']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundSkipsErrorCharges(): void
    {
        // arrange - charge1 failed, it never moved money
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(3, 'complete'),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(50, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            [
                new ChargeHistoryItem(
                    'charge1', 'date1', Amount::fromFloat(50, Currency::getDefault()), 'Error',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge2', 'date1', Amount::fromFloat(50, Currency::getDefault()), 'Success',
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
                Amount::fromFloat(30, Currency::getDefault()),
                'ref-text'
            ]
        );

        // assert - only charge2 should be refunded
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertCount(1, $methodCallHistory);
        self::assertEquals('charge2', $methodCallHistory[0]['chargeId']);
        self::assertEquals(30, $methodCallHistory[0]['amount']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundSkipsErrorChargeWhenSplittingAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(3, 'complete'),
            Amount::fromFloat(130, Currency::getDefault()),
            Amount::fromFloat(80, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            [
                new ChargeHistoryItem(
                    'charge1', 'date1', Amount::fromFloat(50, Currency::getDefault()), 'Error',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge2', 'date1', Amount::fromFloat(40, Currency::getDefault()), 'Success',
                    Amount::fromFloat(0, Currency::getDefault()), 'type', 'id'
                ),
                new ChargeHistoryItem(
                    'charge3', 'date1', Amount::fromFloat(40, Currency::getDefault()), 'Success',
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
                Amount::fromFloat(60, Currency::getDefault()),
                'ref-text'
            ]
        );

        // assert - the error charge is skipped, the amount is split over the successful ones
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertCount(2, $methodCallHistory);
        self::assertEquals('charge2', $methodCallHistory[0]['chargeId']);
        self::assertEquals(40, $methodCallHistory[0]['amount']);
        self::assertEquals('charge3', $methodCallHistory[1]['chargeId']);
        self::assertEquals(20, $methodCallHistory[1]['amount']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateCustomerWithCustomerId(): void
    {
        // arrange
        $customer = new Customer();
        $customer->setId('s-cst-123');
        $customer->setFirstname('John');
        $customer->setLastname('Doe');

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'updateCustomer'], [
            'orderId',
            $customer
        ]);

        // assert
        $updateHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('updateCustomer');
        self::assertNotEmpty($updateHistory);
        self::assertEquals('s-cst-123', $updateHistory[0]['customer']->getId());
        self::assertEquals('John', $updateHistory[0]['customer']->getFirstname());
        self::assertEquals('Doe', $updateHistory[0]['customer']->getLastname());

        $fetchHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('fetchPayment');
        self::assertEmpty($fetchHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateCustomerTransactionHistoryNotFound(): void
    {
        // arrange
        $customer = new Customer();
        $customer->setFirstname('John');

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'updateCustomer'], [
            'orderId',
            $customer
        ]);

        // assert
        $updateHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('updateCustomer');
        self::assertEmpty($updateHistory);

        $fetchHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('fetchPayment');
        self::assertEmpty($fetchHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateCustomerFromPayment(): void
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

        $existingCustomer = new Customer();
        $existingCustomer->setId('s-cst-existing');
        $existingCustomer->setFirstname('OldFirst');
        $existingCustomer->setLastname('OldLast');
        $existingCustomer->setEmail('old@example.com');

        $payment = new Payment();
        $ref = new \ReflectionProperty(Payment::class, 'customer');
        $ref->setAccessible(true);
        $ref->setValue($payment, $existingCustomer);
        $this->unzerFactory->getMockUnzer()->setPayment($payment);

        $updateCustomer = new Customer();
        $updateCustomer->setFirstname('NewFirst');
        $updateCustomer->setEmail('new@example.com');

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'updateCustomer'], [
            'orderId',
            $updateCustomer
        ]);

        // assert
        $fetchHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('fetchPayment');
        self::assertNotEmpty($fetchHistory);
        self::assertEquals('orderId', $fetchHistory[0]['paymentId']);

        $updateHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('updateCustomer');
        self::assertNotEmpty($updateHistory);
        /** @var Customer $updatedCustomer */
        $updatedCustomer = $updateHistory[0]['customer'];
        self::assertEquals('s-cst-existing', $updatedCustomer->getId());
        self::assertEquals('NewFirst', $updatedCustomer->getFirstname());
        self::assertEquals('OldLast', $updatedCustomer->getLastname());
        self::assertEquals('new@example.com', $updatedCustomer->getEmail());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateCustomerNoCustomerOnPayment(): void
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

        $payment = new Payment();
        $this->unzerFactory->getMockUnzer()->setPayment($payment);

        $customer = new Customer();
        $customer->setFirstname('John');

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'updateCustomer'], [
            'orderId',
            $customer
        ]);

        // assert
        $updateHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('updateCustomer');
        self::assertEmpty($updateHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeAmountNullUsesTotalAmount(): void
    {
        // arrange
        $transactionHistory = $this->authorizedTransactionHistory('card');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], ['orderId', null]);

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertCount(1, $methodCallHistory);
        self::assertEquals('paymentId', $methodCallHistory[0]['payment']);
        self::assertEquals(100.0, $methodCallHistory[0]['charge']->getAmount());
        self::assertEquals('orderId', $methodCallHistory[0]['charge']->getOrderId());
        self::assertTrue($result->isCharged());
        self::assertSame($methodCallHistory[0]['charge'], $result->getCharge());
        self::assertEquals('orderId', $result->getTransactionHistory()->getOrderId());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeNoAuthorizedItem(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(1, 'pending'),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(100, Currency::getDefault())
        );
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
            'orderId',
            Amount::fromFloat(10, Currency::getDefault()),
        ]);

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment');
        self::assertEmpty($methodCallHistory);
        self::assertEquals(ChargeResult::SKIPPED, $result->getStatus());
        self::assertEquals(ChargeSkipReason::NO_AUTHORIZATION, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationUplCancelsByOrderId(): void
    {
        // arrange
        $transactionHistory = $this->authorizedTransactionHistory(PaymentMethodTypes::UNZER_INVOICE);
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'cancelOrder'], [
            'orderId',
            Amount::fromFloat(20, Currency::getDefault()),
            'upl-ref'
        ]);

        // assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment');
        self::assertCount(1, $methodCallHistory);
        self::assertEquals('orderId', $methodCallHistory[0]['payment']);
        /** @var Cancellation $cancellation */
        $cancellation = $methodCallHistory[0]['cancellation'];
        self::assertEquals(20, $cancellation->getAmount());
        self::assertEquals('upl-ref', $cancellation->getPaymentReference());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateCustomerAppliesSalutationLanguageAndNewCompanyInfo(): void
    {
        // arrange
        $this->saveCustomerTransactionHistory();

        $existingCustomer = new Customer();
        $existingCustomer->setId('s-cst-existing');
        $this->setPaymentCustomer($existingCustomer);

        $owner = (new CompanyOwner())->setFirstname('Owner')->setLastname('Last')->setBirthdate('1980-01-01');
        $updateCompanyInfo = (new CompanyInfo())
            ->setRegistrationType('registered')
            ->setCommercialRegisterNumber('HRB 123')
            ->setFunction('OWNER')
            ->setCompanyType('company')
            ->setCommercialSector('RETAIL')
            ->setOwner($owner);

        $updateCustomer = new Customer();
        $updateCustomer->setSalutation('mrs');
        $updateCustomer->setLanguage('de');
        $updateCustomer->setCompanyInfo($updateCompanyInfo);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'updateCustomer'], [
            'orderId',
            $updateCustomer
        ]);

        // assert
        $updatedCustomer = $this->updatedCustomer();
        self::assertEquals('mrs', $updatedCustomer->getSalutation());
        self::assertEquals('de', $updatedCustomer->getLanguage());

        $companyInfo = $updatedCustomer->getCompanyInfo();
        self::assertNotNull($companyInfo);
        self::assertEquals('registered', $companyInfo->getRegistrationType());
        self::assertEquals('HRB 123', $companyInfo->getCommercialRegisterNumber());
        self::assertEquals('OWNER', $companyInfo->getFunction());
        self::assertEquals('company', $companyInfo->getCompanyType());
        self::assertEquals('RETAIL', $companyInfo->getCommercialSector());
        self::assertSame($owner, $companyInfo->getOwner());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateCustomerMergesIntoExistingCompanyInfo(): void
    {
        // arrange
        $this->saveCustomerTransactionHistory();

        $existingOwner = (new CompanyOwner())->setFirstname('OldOwner')->setLastname('OldLast')->setBirthdate('1970-01-01');
        $existingCompanyInfo = (new CompanyInfo())
            ->setRegistrationType('registered')
            ->setCommercialRegisterNumber('HRB 1')
            ->setCommercialSector('RETAIL')
            ->setOwner($existingOwner);

        $existingCustomer = new Customer();
        $existingCustomer->setId('s-cst-existing');
        $existingCustomer->setCompanyInfo($existingCompanyInfo);
        $this->setPaymentCustomer($existingCustomer);

        // OTHER is the SDK default sector, it must not overwrite the stored one.
        $updateCompanyInfo = (new CompanyInfo())
            ->setCommercialRegisterNumber('HRB 2')
            ->setCommercialSector('OTHER')
            ->setOwner((new CompanyOwner())->setFirstname('NewOwner'));

        $updateCustomer = new Customer();
        $updateCustomer->setCompanyInfo($updateCompanyInfo);

        // act
        StoreContext::doWithStore('1', [$this->orderManagementService, 'updateCustomer'], [
            'orderId',
            $updateCustomer
        ]);

        // assert
        $companyInfo = $this->updatedCustomer()->getCompanyInfo();
        self::assertSame($existingCompanyInfo, $companyInfo);
        self::assertEquals('registered', $companyInfo->getRegistrationType());
        self::assertEquals('HRB 2', $companyInfo->getCommercialRegisterNumber());
        self::assertEquals('RETAIL', $companyInfo->getCommercialSector());

        $owner = $companyInfo->getOwner();
        self::assertSame($existingOwner, $owner);
        self::assertEquals('NewOwner', $owner->getFirstname());
        self::assertEquals('OldLast', $owner->getLastname());
        self::assertEquals('1970-01-01', $owner->getBirthdate());
    }

    /**
     * @param string $type
     *
     * @return TransactionHistory
     */
    private function authorizedTransactionHistory(string $type): TransactionHistory
    {
        $transactionHistory = new TransactionHistory(
            $type,
            'orderId',
            'EUR',
            new PaymentState(1, 'pending'),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(100, Currency::getDefault())
        );
        $transactionHistory->collection()->add(new AuthorizeHistoryItem(
            'paymentId', '11.11.2011.', Amount::fromFloat(100, Currency::getDefault()),
            'authorized', Amount::fromFloat(0, Currency::getDefault()), $type, 'paymentId'
        ));

        return $transactionHistory;
    }

    /**
     * @return void
     */
    private function saveCustomerTransactionHistory(): void
    {
        $this->transactionHistoryService->saveTransactionHistory($this->authorizedTransactionHistory('card'));
    }

    /**
     * @param Customer $customer
     *
     * @return void
     */
    private function setPaymentCustomer(Customer $customer): void
    {
        $payment = new Payment();
        $ref = new \ReflectionProperty(Payment::class, 'customer');
        $ref->setAccessible(true);
        $ref->setValue($payment, $customer);
        $this->unzerFactory->getMockUnzer()->setPayment($payment);
    }

    /**
     * @return Customer
     */
    private function updatedCustomer(): Customer
    {
        $updateHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('updateCustomer');
        self::assertCount(1, $updateHistory);

        return $updateHistory[0]['customer'];
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeSkippedPaymentCanceled(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(
            $this->chargeableHistory(UnzerPaymentState::STATE_CANCELED, 'canceled', 100)
        );

        // act
        $result = $this->charge(10);

        // assert
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment'));
        self::assertEquals(ChargeSkipReason::PAYMENT_CANCELED, $result->getSkipReason());
        self::assertEquals('orderId', $result->getTransactionHistory()->getOrderId());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeSkippedPaymentNotCreated(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(
            $this->chargeableHistory(UnzerPaymentState::STATE_CREATE, 'create', 100)
        );

        // act
        $result = $this->charge(10);

        // assert
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment'));
        self::assertEquals(ChargeSkipReason::PAYMENT_NOT_CREATED, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeSkippedNothingRemaining(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(
            $this->chargeableHistory(UnzerPaymentState::STATE_COMPLETED, 'completed', 0)
        );

        // act
        $result = $this->charge(10);

        // assert
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment'));
        self::assertEquals(ChargeSkipReason::NOTHING_REMAINING, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeSkippedAmountExceedsRemaining(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(
            $this->chargeableHistory(UnzerPaymentState::STATE_PARTLY, 'partly', 20)
        );

        // act
        $result = $this->charge(30);

        // assert
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment'));
        self::assertEquals(ChargeSkipReason::AMOUNT_EXCEEDS_REMAINING, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeSkippedNotNecessaryWhenIntegrationOverridesIsChargeNecessary(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(
            $this->chargeableHistory(UnzerPaymentState::STATE_PENDING, 'pending', 100)
        );
        $this->orderManagementService = new class ($this->unzerFactory, $this->transactionHistoryService)
            extends OrderManagementService {
            protected function isChargeNecessary(TransactionHistory $transactionHistory, ?Amount $amountToCharge): bool
            {
                return false;
            }
        };

        // act
        $result = $this->charge(10);

        // assert
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment'));
        self::assertEquals(ChargeSkipReason::NOT_NECESSARY, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testFullChargeStillAsksAnIntegrationOverrideOfIsChargeNecessary(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->authorizedTransactionHistory('card'));
        $this->orderManagementService = new class ($this->unzerFactory, $this->transactionHistoryService)
            extends OrderManagementService {
            protected function isChargeNecessary(TransactionHistory $transactionHistory, ?Amount $amountToCharge): bool
            {
                return false;
            }
        };

        // act
        $result = StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], ['orderId', null]);

        // assert
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment'));
        self::assertEquals(ChargeSkipReason::NOT_NECESSARY, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeSkippedReasonFromOverriddenGetChargeSkipReason(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(
            $this->chargeableHistory(UnzerPaymentState::STATE_PENDING, 'pending', 100)
        );
        $this->orderManagementService = new class ($this->unzerFactory, $this->transactionHistoryService)
            extends OrderManagementService {
            protected function getChargeSkipReason(TransactionHistory $transactionHistory, ?Amount $amountToCharge): ?string
            {
                return parent::getChargeSkipReason($transactionHistory, $amountToCharge)
                    ?? ChargeSkipReason::ALREADY_CHARGED;
            }
        };

        // act
        $result = $this->charge(10);

        // assert
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('performChargeOnPayment'));
        self::assertEquals(ChargeSkipReason::ALREADY_CHARGED, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeFetchHookExceptionPropagates(): void
    {
        // arrange
        $this->orderManagementService = new class ($this->unzerFactory, $this->transactionHistoryService)
            extends OrderManagementService {
            protected function fetchTransactionHistoryForCharge(string $orderId): ?TransactionHistory
            {
                throw new ChargePaymentNotReadableException(
                    new TranslatableLabel('Unzer payment could not be read.', 'orderManagement.chargePaymentNotReadable')
                );
            }
        };

        // assert
        $this->expectException(ChargePaymentNotReadableException::class);

        // act
        $this->charge(10);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testChargeUnzerExceptionPropagates(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(
            $this->chargeableHistory(UnzerPaymentState::STATE_PENDING, 'pending', 100)
        );
        $this->unzerFactory->getMockUnzer()->setThrowOnPerformChargeOnPayment(true);

        // assert
        $this->expectException(UnzerApiException::class);

        // act
        $this->charge(10);
    }

    /**
     * @param float $amount
     *
     * @return ChargeResult
     *
     * @throws Exception
     */
    private function charge(float $amount): ChargeResult
    {
        return StoreContext::doWithStore('1', [$this->orderManagementService, 'chargeOrder'], [
            'orderId',
            Amount::fromFloat($amount, Currency::getDefault()),
            'charge-ref'
        ]);
    }

    /**
     * @param int $stateId
     * @param string $stateName
     * @param float $remaining
     *
     * @return TransactionHistory A valid history with an authorization of 100.
     */
    private function chargeableHistory(int $stateId, string $stateName, float $remaining): TransactionHistory
    {
        $transactionHistory = new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState($stateId, $stateName),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(100 - $remaining, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat($remaining, Currency::getDefault())
        );
        $transactionHistory->collection()->add(new AuthorizeHistoryItem(
            'paymentId', '11.11.2011.', Amount::fromFloat(100, Currency::getDefault()),
            'authorized', Amount::fromFloat(0, Currency::getDefault()), 'card', 'paymentId'
        ));

        return $transactionHistory;
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundReturnsTheCancellationOfOneCharge(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->refundableHistory('card', [[100, 0]]));

        // act
        $result = $this->refund(30);

        // assert
        self::assertEquals(RefundResult::REFUNDED, $result->getStatus());
        self::assertTrue($result->isRefunded());
        self::assertCount(1, $result->getCancellations());
        self::assertEquals(30.0, $result->getRefundedAmount());
        self::assertEquals(['s-cnl-1'], $result->toArray()['cancellationIds']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundOverTwoChargesReturnsBothCancellations(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->refundableHistory('card', [[20, 0], [50, 0]]));

        // act
        $result = $this->refund(40);

        // assert
        $calls = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById');
        self::assertCount(2, $calls);
        self::assertEquals(20, $calls[0]['amount']);
        self::assertEquals(20, $calls[1]['amount']);
        self::assertEquals(RefundResult::REFUNDED, $result->getStatus());
        self::assertCount(2, $result->getCancellations());
        self::assertEquals(40.0, $result->getRefundedAmount());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundByPaymentReturnsTheCancellation(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(
            $this->refundableHistory(PaymentMethodTypes::UNZER_INVOICE, [[100, 0]])
        );

        // act
        $result = $this->refund(30);

        // assert
        self::assertCount(1, $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargedPayment'));
        self::assertEquals(RefundResult::REFUNDED, $result->getStatus());
        self::assertEquals(30.0, $result->getRefundedAmount());
    }

    /**
     * Refunded 80 of 100 earlier, a refund of 50: only 20 is left. Before, the 20 was refunded and the call
     * returned as if 50 had been.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testRefundAboveTheRefundableAmountIsNotSent(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->refundableHistory('card', [[100, 80]]));

        // act
        $result = $this->refund(50);

        // assert
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById'));
        self::assertEquals(RefundSkipReason::AMOUNT_EXCEEDS_REFUNDABLE, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundOfTheWholeRefundableAmountIsSent(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->refundableHistory('card', [[100, 80]]));

        // act
        $result = $this->refund(20);

        // assert
        self::assertEquals(RefundResult::REFUNDED, $result->getStatus());
        self::assertEquals(20.0, $result->getRefundedAmount());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundSkipReasonPerPaymentState(): void
    {
        $cases = [
            [UnzerPaymentState::STATE_PENDING, 'pending', RefundSkipReason::PAYMENT_PENDING],
            [UnzerPaymentState::STATE_CANCELED, 'canceled', RefundSkipReason::PAYMENT_CANCELED],
            [UnzerPaymentState::STATE_CREATE, 'create', RefundSkipReason::PAYMENT_NOT_CREATED],
        ];

        foreach ($cases as [$stateId, $stateName, $reason]) {
            // arrange
            $this->transactionHistoryService->saveTransactionHistory(
                $this->refundableHistory('card', [[100, 0]], $stateId, $stateName)
            );

            // act
            $result = $this->refund(10);

            // assert
            self::assertEquals($reason, $result->getSkipReason(), $stateName);
        }

        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundSkippedWhenThePaymentIsNotFound(): void
    {
        // act
        $result = $this->refund(10);

        // assert
        self::assertEquals(RefundSkipReason::PAYMENT_NOT_FOUND, $result->getSkipReason());
        self::assertNull($result->getTransactionHistory());
    }

    /**
     * The first charge is refunded, the second refuses: the refunded part is reported, not lost.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testPartialRefundWhenALaterChargeFails(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->refundableHistory('card', [[20, 0], [50, 0]]));
        $this->unzerFactory->getMockUnzer()->setThrowOnCancelChargeCall(2);

        // act
        $result = $this->refund(40);

        // assert
        self::assertEquals(RefundResult::PARTIAL, $result->getStatus());
        self::assertFalse($result->isRefunded());
        self::assertCount(1, $result->getCancellations());
        self::assertEquals(20.0, $result->getRefundedAmount());
        self::assertInstanceOf(UnzerApiException::class, $result->getError());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testFirstCancellationFailureEscapes(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->refundableHistory('card', [[20, 0], [50, 0]]));
        $this->unzerFactory->getMockUnzer()->setThrowOnCancelChargeCall(1);

        // assert
        $this->expectException(UnzerApiException::class);

        // act
        $this->refund(40);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundStillAsksAnIntegrationOverrideOfIsRefundNecessary(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->refundableHistory('card', [[100, 0]]));
        $this->orderManagementService = new class ($this->unzerFactory, $this->transactionHistoryService)
            extends OrderManagementService {
            protected function isRefundNecessary(TransactionHistory $transactionHistory, Amount $amountToRefund): bool
            {
                return false;
            }
        };

        // act
        $result = $this->refund(10);

        // assert
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelChargeById'));
        self::assertEquals(RefundSkipReason::NOT_NECESSARY, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefundUsesTheIntegrationFetchHook(): void
    {
        // arrange
        $history = $this->refundableHistory('card', [[100, 0]]);
        $this->orderManagementService = new class ($this->unzerFactory, $this->transactionHistoryService, $history)
            extends OrderManagementService {
            private TransactionHistory $history;

            public function __construct($unzerFactory, $transactionHistoryService, TransactionHistory $history)
            {
                parent::__construct($unzerFactory, $transactionHistoryService);
                $this->history = $history;
            }

            protected function fetchTransactionHistoryForRefund(string $orderId): ?TransactionHistory
            {
                return $this->history;
            }
        };

        // act
        $result = $this->refund(10);

        // assert
        self::assertEquals(RefundResult::REFUNDED, $result->getStatus());
    }

    /**
     * @param float $amount
     *
     * @return RefundResult
     *
     * @throws Exception
     */
    private function refund(float $amount): RefundResult
    {
        return StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'refundOrder'],
            ['orderId', Amount::fromFloat($amount, Currency::getDefault()), 'refund-ref']
        );
    }

    /**
     * @param string $type Payment method type.
     * @param array<array{0: float, 1: float}> $charges Charged and already refunded amount per successful charge.
     * @param int $stateId
     * @param string $stateName
     *
     * @return TransactionHistory
     */
    private function refundableHistory(
        string $type,
        array $charges,
        int $stateId = UnzerPaymentState::STATE_COMPLETED,
        string $stateName = 'completed'
    ): TransactionHistory {
        $items = [];
        $charged = 0.0;
        $cancelled = 0.0;

        foreach ($charges as $index => [$amount, $refunded]) {
            $items[] = new ChargeHistoryItem(
                'charge' . ($index + 1),
                'date',
                Amount::fromFloat($amount, Currency::getDefault()),
                'success',
                Amount::fromFloat($refunded, Currency::getDefault()),
                'charge',
                's-pay-1'
            );
            $charged += $amount;
            $cancelled += $refunded;
        }

        return new TransactionHistory(
            $type,
            'orderId',
            'EUR',
            new PaymentState($stateId, $stateName),
            Amount::fromFloat($charged, Currency::getDefault()),
            Amount::fromFloat($charged, Currency::getDefault()),
            Amount::fromFloat($cancelled, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            $items
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelReturnsTheCancellation(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->authorizedTransactionHistory('card'));

        // act
        $result = $this->cancel(null, 'void-ref');

        // assert
        self::assertEquals(CancelResult::CANCELED, $result->getStatus());
        self::assertTrue($result->isCanceled());
        self::assertEquals('s-cnl-1', $result->getCancellation()->getId());
        self::assertEquals('void-ref', $result->getCancellation()->getPaymentReference());
        self::assertEquals(100.0, $result->getCanceledAmount());
        self::assertEquals(
            ['status' => 'canceled', 'skipReason' => null, 'cancellationId' => 's-cnl-1', 'canceledAmount' => 100.0],
            $result->toArray()
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelUplReturnsTheCancellation(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(
            $this->authorizedTransactionHistory(PaymentMethodTypes::UNZER_INVOICE)
        );

        // act
        $result = $this->cancel(null, 'void-ref');

        // assert
        self::assertTrue($result->isCanceled());
        self::assertEquals(
            'orderId',
            $this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment')[0]['payment']
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelSkippedPaymentNotFound(): void
    {
        // act
        $result = $this->cancel(null);

        // assert
        self::assertEquals(CancelResult::SKIPPED, $result->getStatus());
        self::assertEquals(CancelSkipReason::PAYMENT_NOT_FOUND, $result->getSkipReason());
        self::assertNull($result->getTransactionHistory());
        self::assertEquals(0.0, $result->getCanceledAmount());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelSkippedNoAuthorization(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            new PaymentState(UnzerPaymentState::STATE_COMPLETED, 'completed'),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault())
        ));

        // act
        $result = $this->cancel(null);

        // assert
        self::assertEquals(CancelSkipReason::NO_AUTHORIZATION, $result->getSkipReason());
        self::assertNotNull($result->getTransactionHistory());
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelWithoutAmountSkipsTheStateChecks(): void
    {
        // arrange
        $transactionHistory = $this->authorizedTransactionHistory('card');
        $transactionHistory->setPaymentState(new PaymentState(UnzerPaymentState::STATE_CANCELED, 'canceled'));
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // act
        $result = $this->cancel(null);

        // assert
        self::assertTrue($result->isCanceled());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelWithAmountSkipReasons(): void
    {
        $cases = [
            CancelSkipReason::PAYMENT_CANCELED => [UnzerPaymentState::STATE_CANCELED, 10.0],
            CancelSkipReason::PAYMENT_NOT_CREATED => [UnzerPaymentState::STATE_CREATE, 10.0],
            CancelSkipReason::AMOUNT_EXCEEDS_REMAINING => [UnzerPaymentState::STATE_PENDING, 150.0],
        ];

        foreach ($cases as $reason => [$stateId, $amount]) {
            // arrange
            $transactionHistory = $this->authorizedTransactionHistory('card');
            $transactionHistory->setPaymentState(new PaymentState($stateId, 'state'));
            $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

            // act
            $result = $this->cancel($amount);

            // assert
            self::assertEquals($reason, $result->getSkipReason());
        }

        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelWithAmountSkippedInvalidHistory(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory(new TransactionHistory(
            'card',
            'orderId',
            'EUR',
            null,
            Amount::fromFloat(100, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(0, Currency::getDefault()),
            Amount::fromFloat(100, Currency::getDefault())
        ));

        // act
        $result = $this->cancel(10);

        // assert
        self::assertEquals(CancelSkipReason::INVALID_HISTORY, $result->getSkipReason());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelStillAsksAnIntegrationOverrideOfIsCancellationNecessary(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->authorizedTransactionHistory('card'));
        $this->orderManagementService = new class ($this->unzerFactory, $this->transactionHistoryService)
            extends OrderManagementService {
            protected function isCancellationNecessary(
                TransactionHistory $transactionHistory,
                ?Amount $amount = null
            ): bool {
                return false;
            }
        };

        // act
        $result = $this->cancel(null);

        // assert
        self::assertEquals(CancelSkipReason::NOT_NECESSARY, $result->getSkipReason());
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('cancelAuthorizedPayment'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelUsesTheFetchHook(): void
    {
        // arrange
        $history = $this->authorizedTransactionHistory('card');
        $this->orderManagementService = new class ($this->unzerFactory, $this->transactionHistoryService, $history)
            extends OrderManagementService {
            private TransactionHistory $history;

            public function __construct($unzerFactory, $transactionHistoryService, TransactionHistory $history)
            {
                parent::__construct($unzerFactory, $transactionHistoryService);
                $this->history = $history;
            }

            protected function fetchTransactionHistoryForCancel(string $orderId): ?TransactionHistory
            {
                return $this->history;
            }
        };

        // act
        $result = $this->cancel(null);

        // assert
        self::assertTrue($result->isCanceled());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancelUnzerErrorEscapes(): void
    {
        // arrange
        $this->transactionHistoryService->saveTransactionHistory($this->authorizedTransactionHistory('card'));
        $this->unzerFactory->getMockUnzer()->setThrowOnCancelAuthorization(true);

        // assert
        $this->expectException(UnzerApiException::class);

        // act
        $this->cancel(null);
    }

    /**
     * @param float|null $amount
     * @param string|null $reference
     *
     * @return CancelResult
     *
     * @throws Exception
     */
    private function cancel(?float $amount, ?string $reference = 'void-ref'): CancelResult
    {
        return StoreContext::doWithStore(
            '1',
            [$this->orderManagementService, 'cancelOrder'],
            ['orderId', $amount === null ? null : Amount::fromFloat($amount, Currency::getDefault()), $reference]
        );
    }
}
