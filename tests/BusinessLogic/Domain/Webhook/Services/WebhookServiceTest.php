<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\Webhook\Services;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Unzer\Core\BusinessLogic\Domain\Webhook\Services\WebhookService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\OrderServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentSDK;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\SdkAmount;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\TransactionSynchronizerServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
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

        $this->unzerFactory = new UnzerFactoryMock();

        $this->transactionSynchronizerServiceMock = new TransactionSynchronizerServiceMock(
            new UnzerFactoryMock(),
            TestServiceRegister::getService(TransactionHistoryService::class),
            new OrderServiceMock(),
            TestServiceRegister::getService(PaymentStatusMapService::class)
        );

        $this->service = new WebhookService(
            $this->unzerFactory,
            $this->transactionSynchronizerServiceMock
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
    public function testTransactionsUpdated(): void
    {
        // Arrange
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setResourceFromEvent($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

        // Assert
        $methodCallHistory = $this->transactionSynchronizerServiceMock->getCallHistory('getAndUpdateTransactionHistoryFromUnzerPayment');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testOrderStatusUpdated(): void
    {
        // Arrange
        $webhook = new Webhook('test', 'charge.succes', 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');
        $unzerMock->setResourceFromEvent($this->generateValidPayment());
        $this->unzerFactory->setMockUnzer($unzerMock);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

        // Assert
        $methodCallHistory = $this->transactionSynchronizerServiceMock->getCallHistory('handleOrderStatusChange');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
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
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

        // Assert
        $methodCallHistory = $this->transactionSynchronizerServiceMock->getCallHistory('handleRefund');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHandleCancellation(): void
    {
        // Arrange
        $webhook = new Webhook('test', WebhookEvents::AUTHORIZE_CANCELED, 'p-key', 'payment1');
        $unzerMock = new UnzerMock('s-priv-test');
        $payment = $this->generateValidPayment();
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

        // Assert
        $methodCallHistory = $this->transactionSynchronizerServiceMock->getCallHistory('handleCancellation');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
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
        $unzerMock->setResourceFromEvent($payment);
        $this->unzerFactory->setMockUnzer($unzerMock);

        // Act
        StoreContext::doWithStore('1', [$this->service, 'handle'], [$webhook]);

        // Assert
        $methodCallHistory = $this->transactionSynchronizerServiceMock->getCallHistory('handleCharge');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('order1', $methodCallHistory[0]['orderId']);
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
