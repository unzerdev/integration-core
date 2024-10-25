<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\Webhook\Services;

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
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Unzer\Core\BusinessLogic\Domain\Webhook\Services\WebhookService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\OrderServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\PaymentStatusMapServiceMock as PaymentStatusMapIntegrationMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentSDK;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentStatusMapServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\SdkAmount;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\TransactionHistoryServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Constants\WebhookEvents;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Chargeback;
use UnzerSDK\Resources\TransactionTypes\Payout;
use UnzerSDK\Resources\TransactionTypes\Shipment;

/**
 * Class WebhookServiceTest.
 *
 * @package BusinessLogic\Domain\Webhook\Services
 */
class WebhookServiceTest extends BaseTestCase
{
    /**
     * @var WebhookService
     */
    public WebhookService $service;

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
        $this->service = new WebhookService(
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
    public function testPaymentFetchedFromEvent(): void
    {
        // Arrange
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setResourceFromEvent($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

        // Assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('fetchResourceFromEvent');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals($webhook->toPayload(), $methodCallHistory[0]['event']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testPaymentNotFetchedFromEvent(): void
    {
        // Arrange
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setResourceFromEvent(new Payout());
        $unzerMock->setPayment($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

        // Assert
        $fetchedResourceHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('fetchResourceFromEvent');
        $fetchedPaymentHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('fetchPayment');

        self::assertNotEmpty($fetchedResourceHistory);
        self::assertEquals($webhook->toPayload(), $fetchedResourceHistory[0]['event']);
        self::assertNotEmpty($fetchedPaymentHistory);
        self::assertEquals('payment1', $fetchedPaymentHistory[0]['paymentId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testNoTransactionHistoryForOrder(): void
    {
        // Arrange
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setResourceFromEvent($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);
        $this->expectException(TransactionHistoryNotFoundException::class);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setResourceFromEvent($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = TransactionHistory::fromUnzerPayment($this->generateValidPayment());
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setResourceFromEvent($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PENDING);
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_CANCELED);
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_COMPLETED);
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_CHARGEBACK);
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
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
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
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
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', WebhookEvents::CHARGE_CANCELED, 'p-key', 'payment1');
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
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', WebhookEvents::CHARGE_CANCELED, 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');

        $payment = $this->generateValidPayment();
        $payment->setState(PaymentState::STATE_PARTLY);
        $amount = new SdkAmount();
        $amount->setCurrency('EUR');
        $amount->setTotal(1000.00);
        $amount->setCharged(1000.00);
        $amount->setCanceled(300.00);
        $amount->setRemaining(0.00);

        $this->orderService->setRefundedAmount(Amount::fromFloat(200, Currency::getDefault()));
        $payment->setAmount($amount);
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', WebhookEvents::PAYMENT_CANCELED, 'p-key', 'payment1');
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
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', WebhookEvents::PAYMENT_CANCELED, 'p-key', 'payment1');
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
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', WebhookEvents::CHARGE_SUCCEEDED, 'p-key', 'payment1');
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
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
        $webhook = new Webhook('test', WebhookEvents::CHARGE_SUCCEEDED, 'p-key', 'payment1');
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
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);
        $transactionHistory = new TransactionHistory('card', 'payment1', 'order1');
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

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
