<?php

namespace Unzer\Core\Tests\BusinessLogic\CheckoutAPI\PaymentPage;

use Exception;
use Unzer\Core\BusinessLogic\ApiFacades\Response\ErrorResponse;
use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response\PaymentResponse;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\MetadataProvider;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidAmountsException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\Payments\Customer\Factory\CustomerFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Factory\BasketFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Factory\PaymentPageFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Services\PaymentPageService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState as DomainPaymentState;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\ConnectionServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\CurrencyServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\KeypairMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentMethodServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentSDK;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\SdkAmount;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Constants\PaypageCheckoutTypes;
use UnzerSDK\Constants\TransactionTypes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\Paypage\PaymentMethodConfig as EmbeddedPaymentMethodConfig;
use UnzerSDK\Resources\EmbeddedResources\Paypage\PaymentMethodsConfigs;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Urls;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Chargeback;
use UnzerSDK\Resources\TransactionTypes\Payout;
use UnzerSDK\Resources\TransactionTypes\Shipment;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Class CheckoutPaymentPageAPITest
 *
 * @package BusinessLogic\CheckoutAPI\PaymentPage
 */
class CheckoutPaymentPageApiTest extends BaseTestCase
{
    private ?UnzerFactoryMock $unzerFactory;
    private PaymentMethodServiceMock $paymentMethodService;

    /**
     * @var ConnectionServiceMock
     */
    private ConnectionServiceMock $connectionService;

    public function setUp(): void
    {
        parent::setUp();

        $this->unzerFactory = (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test'));
        $this->paymentMethodService = new PaymentMethodServiceMock(
            $this->unzerFactory,
            TestServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class),
            new CurrencyServiceMock()
        );

        TestServiceRegister::registerService(UnzerFactory::class, function () {
            return $this->unzerFactory;
        });
        TestServiceRegister::registerService(PaymentMethodService::class, function () {
            return $this->paymentMethodService;
        });
        TestServiceRegister::registerService(PaymentPageService::class, function () {
            return new PaymentPageService(
                $this->unzerFactory,
                TestServiceRegister::getService(PaymentMethodService::class),
                TestServiceRegister::getService(TransactionHistoryService::class),
                TestServiceRegister::getService(PaymentPageFactory::class),
                TestServiceRegister::getService(CustomerFactory::class),
                ServiceRegister::getService(BasketFactory::class),
                ServiceRegister::getService(MetadataProvider::class)
            );
        });

        $this->connectionService = new ConnectionServiceMock(
            new UnzerFactoryMock(),
            TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
            TestServiceRegister::getService(WebhookSettingsRepositoryInterface::class),
            TestServiceRegister::getService(EncryptorInterface::class),
            TestServiceRegister::getService(WebhookUrlServiceInterface::class)
        );
        TestServiceRegister::registerService(
            ConnectionService::class,
            function () {
                return $this->connectionService;
            }
        );

        $this->setMockPaymentMethods();
    }

    public function testMissingPaymentMethodTypeRequest(): void
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);

        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        $request = new PaymentPageCreateRequest(
            PaymentMethodTypes::ALI_PAY,
            'test123',
            Amount::fromFloat(123.23, Currency::getDefault()),
            'test.my.shop.com'
        );

        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->create($request);

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertNotEmpty($response->toArray());
        self::assertInstanceOf(ErrorResponse::class, $response);
        self::assertStringContainsString('config for type: alipay not found', $response->toArray()['errorMessage']);
        self::assertEquals('paymentMethod.configNotFound', $response->toArray()['errorCode']);
    }

    public function testAuthorizedPaymentPageWithMinimalRequest(): void
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);

        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        $paymentMethodsConfigs = new PaymentMethodsConfigs();
        $paymentMethodsConfigs->setDefault(new EmbeddedPaymentMethodConfig(false));
        $paymentMethodsConfigs->addMethodConfig('eps', (new EmbeddedPaymentMethodConfig(false))
            ->setEnabled(true));

        $expectedPayPageRequest = new Paypage(123.23, Currency::getDefault(), TransactionTypes::AUTHORIZATION);
        $expectedPayPageRequest
            ->setOrderId('test-order-123')
            ->setPaymentMethodsConfigs($paymentMethodsConfigs)
            ->setType("embedded")
            ->setCheckoutType(PaypageCheckoutTypes::PAYMENT_ONLY);

        $urls = new Urls();

        $urls->setReturnSuccess('test.my.shop.com')
            ->setReturnFailure('test.my.shop.com')
            ->setReturnPending('test.my.shop.com')
            ->setReturnCancel('test.my.shop.com');

        $expectedPayPageRequest->setUrls($urls);

        $expectedPayPageRequest->setResources(new Resources());

        $this->unzerFactory->getMockUnzer()->setPayPageData(
            ['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com', 'paymentId' => 'test-payment-123']
        );

        $request = new PaymentPageCreateRequest(
            PaymentMethodTypes::EPS,
            'test-order-123',
            Amount::fromFloat(123.23, Currency::getDefault()),
            'test.my.shop.com'
        );

        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->create($request);

        // Assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('createPaypage');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals($expectedPayPageRequest, $methodCallHistory[0]['paypage']);
        self::assertTrue($response->isSuccessful());
        self::assertNotEmpty($response->toArray());
        self::assertEquals(
            ['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com', 'publicKey' => 'publicKeyTest'],
            $response->toArray()
        );
        self::assertTransactionHistory(
            new TransactionHistory(PaymentMethodTypes::EPS, 'test-order-123', 'EUR')
        );
    }

    public function testAddClickToPayWhenEnabled(): void
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['applepay', 'googlepay', 'card', 'clicktopay']);

        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        $paymentMethodsConfigs = new PaymentMethodsConfigs();
        $paymentMethodsConfigs->setDefault(new EmbeddedPaymentMethodConfig(false));
        $paymentMethodsConfigs->addMethodConfig('cards', (new EmbeddedPaymentMethodConfig(false))
            ->setEnabled(true));
        $paymentMethodsConfigs->addMethodConfig('clicktopay',
            (new EmbeddedPaymentMethodConfig(false))->setEnabled(true));

        $expectedPayPageRequest = new Paypage(123.23, Currency::getDefault(), TransactionTypes::AUTHORIZATION);
        $expectedPayPageRequest
            ->setOrderId('test-order-123')
            ->setPaymentMethodsConfigs($paymentMethodsConfigs)
            ->setType("embedded")
            ->setCheckoutType(PaypageCheckoutTypes::PAYMENT_ONLY);

        $urls = new Urls();

        $urls->setReturnSuccess('test.my.shop.com')
            ->setReturnFailure('test.my.shop.com')
            ->setReturnPending('test.my.shop.com')
            ->setReturnCancel('test.my.shop.com');

        $expectedPayPageRequest->setUrls($urls);

        $expectedPayPageRequest->setResources(new Resources());

        $this->unzerFactory->getMockUnzer()->setPayPageData(
            ['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com', 'paymentId' => 'test-payment-123']
        );

        $request = new PaymentPageCreateRequest(
            PaymentMethodTypes::CARDS,
            'test-order-123',
            Amount::fromFloat(123.23, Currency::getDefault()),
            'test.my.shop.com'
        );

        $this->paymentMethodService->setMockPaymentMethod(
            new PaymentMethodConfig(
                PaymentMethodTypes::CARDS,
                true,
                BookingMethod::authorize(),
                false,
                null,
                null,
                'test',
                null,
                null,
                null,
                [],
                true
            )
        );

        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->create($request);

        // Assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('createPaypage');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals($expectedPayPageRequest, $methodCallHistory[0]['paypage']);
        self::assertTrue($response->isSuccessful());
    }

    public function testChargePaymentPageWithMinimalRequest(): void
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);

        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        $paymentMethodsConfigs = new PaymentMethodsConfigs();
        $paymentMethodsConfigs->setDefault(new EmbeddedPaymentMethodConfig(false));
        $paymentMethodsConfigs->addMethodConfig('cards', (new EmbeddedPaymentMethodConfig(false))
            ->setEnabled(true));

        $expectedPayPageRequest = new Paypage(123.23, Currency::getDefault(), TransactionTypes::CHARGE);
        $expectedPayPageRequest
            ->setOrderId('test-order-123')
            ->setPaymentMethodsConfigs($paymentMethodsConfigs)
            ->setType("embedded")
            ->setCheckoutType(PaypageCheckoutTypes::PAYMENT_ONLY);

        $urls = new Urls();

        $urls->setReturnSuccess('test.my.shop.com')
            ->setReturnFailure('test.my.shop.com')
            ->setReturnPending('test.my.shop.com')
            ->setReturnCancel('test.my.shop.com');

        $expectedPayPageRequest->setUrls($urls);

        $expectedPayPageRequest->setResources(new Resources());

        $this->unzerFactory->getMockUnzer()->setPayPageData(
            ['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com', 'paymentId' => 'test-payment-123']
        );

        $request = new PaymentPageCreateRequest(
            PaymentMethodTypes::CARDS,
            'test-order-123',
            Amount::fromFloat(123.23, Currency::getDefault()),
            'test.my.shop.com'
        );

        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->create($request);

        // Assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('createPaypage');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals($expectedPayPageRequest, $methodCallHistory[0]['paypage']);
        self::assertTrue($response->isSuccessful());
        self::assertNotEmpty($response->toArray());
        self::assertEquals(
            ['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com', 'publicKey' => 'publicKeyTest'],
            $response->toArray()
        );
        self::assertTransactionHistory(
            new TransactionHistory(PaymentMethodTypes::CARDS, 'test-order-123', 'EUR')
        );
    }

    public function testCheckForUnknownPaymentStatus(): void
    {
        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->getPaymentState('test-order-123');

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertNotEmpty($response->toArray());
        self::assertStringContainsString(
            'history for orderId: test-order-123 not found',
            $response->toArray()['errorMessage']
        );
        self::assertEquals('transactionHistory.notFound', $response->toArray()['errorCode']);
    }

    /**
     * @throws UnzerApiException
     */
    public function testCheckSuccessfulPaymentStatus(): void
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);

        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        $this->unzerFactory->getMockUnzer()
            ->setPayPageData(
                ['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com', 'paymentId' => 'test-payment-123']
            )->setPayment($this->generateValidPayment()->setState(PaymentState::STATE_COMPLETED));

        CheckoutAPI::get()->paymentPage('1')->create(
            new PaymentPageCreateRequest(
                PaymentMethodTypes::CARDS,
                'test-order-123',
                Amount::fromFloat(123.23, Currency::getDefault()),
                'test.my.shop.com'
            )
        );

        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->getPaymentState('test-order-123');

        // Assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('fetchPayment');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('test-order-123', $methodCallHistory[0]['paymentId']);
        self::assertTrue($response->isSuccessful());
        self::assertEquals(
            ['id' => PaymentState::STATE_COMPLETED, 'name' => PaymentState::STATE_NAME_COMPLETED],
            $response->toArray()
        );
        self::assertEquals(
            new DomainPaymentState(
                PaymentState::STATE_COMPLETED,
                PaymentState::STATE_NAME_COMPLETED
            ),
            $response->getPaymentState()
        );
    }

    /**
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws PaymentConfigNotFoundException
     * @throws TransactionHistoryNotFoundException
     */
    public function testCheckSuccessfulPayment(): void
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['cards']);
        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        $this->unzerFactory->getMockUnzer()
            ->setPayPageData(
                ['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com', 'paymentId' => 'test-payment-123']
            )->setPayment($this->generateValidPayment()->setState(PaymentState::STATE_COMPLETED));

        CheckoutAPI::get()->paymentPage('1')->create(
            new PaymentPageCreateRequest(
                PaymentMethodTypes::CARDS,
                'test-order-123',
                Amount::fromFloat(123.23, Currency::getDefault()),
                'test.my.shop.com'
            )
        );

        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->getPaymentById('test-order-123');

        // Assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('fetchPayment');
        self::assertNotEmpty($methodCallHistory);
        self::assertTrue($response->isSuccessful());
        self::assertInstanceOf(PaymentResponse::class, $response);
        self::assertEquals('payment1', $response->toArray()['id']);
        self::assertEquals(1, $response->toArray()['state']);
        self::assertEquals('completed', $response->toArray()['stateName']);
        self::assertEquals('EUR', $response->toArray()['currency']);
        self::assertEquals(1000.0, $response->toArray()['total']);
    }

    /**
     * @return void
     * @throws ConnectionSettingsNotFoundException
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function testMissingTransactionHistory(): void
    {
        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->getPaymentById('unknown-order');

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertArrayHasKey('errorMessage', $response->toArray());
        self::assertStringContainsString('Transaction history for orderId: unknown-order not found', $response->toArray()['errorMessage']);
        self::assertEquals('transactionHistory.notFound', $response->toArray()['errorCode']);
    }


    private static function assertTransactionHistory(TransactionHistory $expected): void
    {
        $transactionHistory = StoreContext::doWithStore('1', static function () use ($expected) {
            /** @var TransactionHistoryService $transactionHistoryService */
            $transactionHistoryService = TestServiceRegister::getService(TransactionHistoryService::class);
            return $transactionHistoryService->getTransactionHistoryByOrderId($expected->getOrderId());
        });

        self::assertInstanceOf(TransactionHistory::class, $transactionHistory);
        self::assertEquals($expected, $transactionHistory);
    }

    /**
     * @return void
     *
     * @throws InvalidAmountsException
     * @throws InvalidTranslatableArrayException
     */
    private function setMockPaymentMethods(): void
    {
        $nameEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps eng'],
            ['locale' => 'de', 'value' => 'Eps De']
        ]);
        $descriptionEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps eng desc'],
            ['locale' => 'de', 'value' => 'Eps De desc']
        ]);

        $nameCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $descriptionCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);

        $this->paymentMethodService->setMockPaymentMethods(
            [
                new PaymentMethodConfig(
                    PaymentMethodTypes::EPS,
                    true,
                    BookingMethod::authorize(),
                    false,
                    $nameEps,
                    $descriptionEps,
                    '2',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('DE', 'Germany'), new Country('FR', 'France')]
                ),
                new PaymentMethodConfig(
                    PaymentMethodTypes::CARDS,
                    true,
                    BookingMethod::charge(),
                    true,
                    $nameCard,
                    $descriptionCard,
                    '1',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('us', 'United States')]
                )
            ]
        );
    }

    private function mockData(string $publicKey, string $privateKey, array $types = [], array $paymentTypes = []): void
    {
        $keypair = new KeypairMock();
        $keypair->setPublicKey($publicKey);
        $keypair->setAvailablePaymentTypes($types);
        $keypair->setPaymentTypes($paymentTypes);
        $unzerMock = new UnzerMock($privateKey);
        $unzerMock->setKeypair($keypair);
        $this->unzerFactory->setMockUnzer($unzerMock);
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
