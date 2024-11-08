<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\TransactionSynchronization\Services;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Service\TransactionSynchronizerService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\OrderServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\CancellationSDK;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentSDK;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentStatusMapServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\SdkAmount;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\TransactionHistoryServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\PaymentStatusMapServiceMock as PaymentStatusMapIntegrationMock;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Chargeback;
use UnzerSDK\Resources\TransactionTypes\Payout;
use UnzerSDK\Resources\TransactionTypes\Shipment;

/**
 * Class TransactionSynchronizerServiceTest.
 *
 * @package BusinessLogic\Domain\TransactionSynchronization\Services
 */
class TransactionSynchronizerServiceTest extends BaseTestCase
{
    /**
     * @var TransactionSynchronizerService
     */
    public TransactionSynchronizerService $service;

    /**
     * @var OrderServiceMock
     */
    public OrderServiceMock $orderService;

    /**
     * @var PaymentStatusMapService
     */
    public PaymentStatusMapService $paymentStatusMapService;

    /**
     * @var TransactionHistoryService
     */
    public TransactionHistoryService $transactionHistoryService;

    /** @var UnzerFactoryMock */
    private UnzerFactoryMock $unzerFactory;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->unzerFactory = new UnzerFactoryMock();
        $this->transactionHistoryService = new TransactionHistoryServiceMock(
            TestServiceRegister::getService(TransactionHistoryRepositoryInterface::class)
        );

        $this->orderService = new OrderServiceMock();
        $this->paymentStatusMapService = new PaymentStatusMapServiceMock(
            TestServiceRegister::getService(PaymentStatusMapRepositoryInterface::class),
            new PaymentStatusMapIntegrationMock()
        );

        $this->paymentStatusMapService->savePaymentStatusMappingSettings([
            PaymentStatus::PAID => '1',
            PaymentStatus::UNPAID => '2',
            PaymentStatus::FULL_REFUND => '3',
            PaymentStatus::CANCELLED => '4',
            PaymentStatus::CHARGEBACK => '5',
            PaymentStatus::COLLECTION => '6',
            PaymentStatus::PARTIAL_REFUND => '7',
            PaymentStatus::DECLINED => '8'
        ]);
        $this->service = new TransactionSynchronizerService(
            $this->unzerFactory,
            $this->transactionHistoryService,
            $this->orderService,
            $this->paymentStatusMapService
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testNoTransactionHistoryForOrder(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setPayment($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);
        $this->expectException(TransactionHistoryNotFoundException::class);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testTransactionsEqualNoSyncNecessary(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setPayment($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = TransactionHistory::fromUnzerPayment($this->generateValidPayment());
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->transactionHistoryService->getCallHistory('saveTransactionHistory');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals(1, $methodCallHistory['count']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testTransactionsUpdated(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setPayment($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->transactionHistoryService->getCallHistory('saveTransactionHistory');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals(2, $methodCallHistory['count']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testOrderStatusChangeToUnpaid(): void
    {
        // Arrange

        $unzerMock = new UnzerMock('s-priv-test');
        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PENDING);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('changeOrderStatus');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
        self::assertEquals('2', $methodCallHistory[0]['statusId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testOrderStatusChangeToCancelled(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');
        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_CANCELED);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('changeOrderStatus');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
        self::assertEquals('4', $methodCallHistory[0]['statusId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testOrderStatusChangeToPaid(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');
        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_COMPLETED);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('changeOrderStatus');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
        self::assertEquals('1', $methodCallHistory[0]['statusId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testOrderStatusChangeToChargeback(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');
        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_CHARGEBACK);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('changeOrderStatus');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
        self::assertEquals('5', $methodCallHistory[0]['statusId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testOrderStatusChangeToPartialRefund(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PARTLY);
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(1000.00);
        $amount->setCanceled(500.00);
        $amount->setRemaining(0.00);
        $payment->setAmount($amount);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('changeOrderStatus');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
        self::assertEquals('7', $methodCallHistory[0]['statusId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testOrderStatusChangeToFullRefund(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PARTLY);
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(1000.00);
        $amount->setCanceled(1000.00);
        $amount->setRemaining(0.00);
        $payment->setAmount($amount);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('changeOrderStatus');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
        self::assertEquals('3', $methodCallHistory[0]['statusId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHandleRefundNotNeededOnShop(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PARTLY);
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(1000.00);
        $amount->setCanceled(300.00);
        $amount->setRemaining(0.00);

        $this->orderService->setRefundedAmount(Amount::fromFloat(300, Currency::getDefault()));
        $payment->setAmount($amount);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('refundOrder');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHandleRefund(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PARTLY);
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(1000.00);
        $amount->setCanceled(300.00);
        $amount->setRemaining(0.00);
        $charge1 = new Charge(50, 'EUR', 'test');
        $charge1->setId('chargeId1');
        $charge1->setDate('2024-10-21 16:58:08');
        $cancellation1 = new CancellationSDK(300);
        $cancellation1->setIsSuccess(true);
        $charge1->addCancellation(new Cancellation(300));
        $payment->addCharge($charge1);

        $charge2 = new Charge(60, 'EUR', 'test');
        $charge2->setId('chargeId2');
        $charge2->setDate('2024-10-21 17:58:08');
        $cancellation2 = new CancellationSDK(300);
        $cancellation2->setIsSuccess(true);
        $charge2->addCancellation($cancellation2);
        $payment->addCharge($charge2);

        $this->orderService->setRefundedAmount(Amount::fromFloat(200, Currency::getDefault()));
        $payment->setAmount($amount);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('refundOrder');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
        self::assertEquals(100, $methodCallHistory[0]['amount']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHandleCancellationNotNeeded(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PARTLY);
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(1000.00);
        $amount->setCanceled(1000.00);
        $amount->setRemaining(0.00);
        $authorization = new Authorization(1000, 'EUR', 'test');
        $authorization->setId('authId');
        $authorization->setDate('2024-10-21 15:58:08');
        $authorization->setCancellations([new Cancellation(1000)]);
        $this->orderService->setCancelledAmount(Amount::fromFloat(1000, Currency::getDefault()));
        $payment->setAmount($amount);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('cancelOrder');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHandleCancellation(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');
        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PARTLY);
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(1000.00);
        $amount->setCanceled(1000.00);
        $amount->setRemaining(0.00);
        $authorization = new Authorization(1000, 'EUR', 'test');
        $authorization->setId('authId');
        $authorization->setDate('2024-10-21 15:58:08');
        $authorization->setCancellations([new Cancellation(1000)]);
        $payment->setAuthorization($authorization);
        $this->orderService->setCancelledAmount(Amount::fromFloat(500, Currency::getDefault()));
        $payment->setAmount($amount);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('cancelOrder');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
        self::assertEquals(500, $methodCallHistory[0]['amount']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHandleChargeNotNeededOnShop(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PARTLY);
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(300.00);
        $amount->setCanceled(300.00);
        $amount->setRemaining(0.00);

        $this->orderService->setChargedAmount(Amount::fromFloat(300, Currency::getDefault()));
        $payment->setAmount($amount);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('chargeOrder');
        self::assertEmpty($methodCallHistory);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHandleCharge(): void
    {
        // Arrange
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PARTLY);
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(300.00);
        $amount->setCanceled(300.00);
        $amount->setRemaining(0.00);

        $this->orderService->setChargedAmount(Amount::fromFloat(200, Currency::getDefault()));
        $payment->setAmount($amount);
        $unzerMock->setPayment($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1', 'EUR');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeTransactions'], ['1']);

        // Assert
        $methodCallHistory = $this->orderService->getCallHistory('chargeOrder');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
        self::assertEquals(100, $methodCallHistory[0]['amount']);
    }

    /**
     * @return PaymentSDK
     *
     * @throws UnzerApiException
     * @throws Exception
     */
    private function generateValidPayment(): PaymentSDK
    {
        $payment = new PaymentSDK();
        $payment->setParentResource(new UnzerMock('s-priv-test'));
        $payment->setPaymentType(new Card('test', '03/30'));
        $payment->setId('payment1');
        $payment->setOrderId('order1');
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(900.00);
        $amount->setCanceled(500.00);
        $amount->setRemaining(100.00);
        $payment->setAmount($amount);

        $authorization = new Authorization(1000, 'EUR', 'test');
        $authorization->setId('authId');
        $authorization->setDate('2024-10-21 15:58:08');
        $payment->setAuthorization($authorization);

        $charge1 = new Charge(50, 'EUR', 'test');
        $charge1->setId('chargeId1');
        $charge1->setDate('2024-10-21 16:58:08');
        $payment->addCharge($charge1);
        $charge2 = new Charge(60, 'EUR', 'test');
        $charge2->setId('chargeId2');
        $charge2->setDate('2024-10-21 17:58:08');
        $payment->addCharge($charge2);

        $reversal = new Cancellation(20);
        $reversal->setId('reversalId');
        $reversal->setDate('2024-10-22 17:58:08');
        $payment->addReversal($reversal);

        $refund = new Cancellation(44);
        $refund->setId('refundId');
        $refund->setDate('2024-10-23 17:58:08');
        $payment->addRefund($refund);

        $shipment = new Shipment();
        $shipment->setId('shipmentId');
        $shipment->setDate('2024-10-24 17:58:08');
        $shipment->setAmount(11);
        $payment->addShipment($shipment);

        $payout = new Payout(21, 'EUR', 'test');
        $payout->setId('payoutId');
        $payout->setDate('2024-10-25 17:58:08');
        $payment->setPayout($payout);

        $chargeBack = new ChargeBack(60);
        $chargeBack->setId('chargeBackId');
        $chargeBack->setDate('2024-10-26 17:58:08');
        $payment->setChargebacks([$chargeBack]);

        return $payment;
    }
}
