<?php

namespace Unzer\Core\Tests\BusinessLogic\CheckoutAPI\CommonFlow;

use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Factory\CommonFlowFactory;
use Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Request\InlinePaymentCreateRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Response\InlinePaymentResponse;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
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
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\Payments\Customer\Factory\CustomerFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Factory\InlinePaymentFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePayment;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Processors\InlinePaymentProcessorInterface;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Services\InlinePaymentService;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Strategy\InlinePaymentStrategyFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Factory\BasketFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Factory\PaymentPageFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Services\PaymentPageService;
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
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\InlinePaymentProcessorMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\KeypairMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentMethodServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\TestBookingMethod;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Resources\TransactionTypes\Charge;

class CommonFlowPaymentTest extends BaseTestCase
{
    private ?UnzerFactoryMock $unzerFactory;
    private PaymentMethodServiceMock $paymentMethodService;
    private ConnectionServiceMock $connectionService;
    private InlinePaymentProcessorInterface $mockInlineProcessor;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockInlineProcessor = new InlinePaymentProcessorMock();
        $this->unzerFactory = (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test'));
        $this->paymentMethodService = new PaymentMethodServiceMock(
            $this->unzerFactory,
            TestServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class),
            new CurrencyServiceMock()
        );

        TestServiceRegister::registerService(UnzerFactory::class, function () {
            return $this->unzerFactory;
        });
        TestServiceRegister::registerService(InlinePaymentProcessorInterface::class, function () {
            return $this->mockInlineProcessor;
        });
        TestServiceRegister::registerService(PaymentMethodService::class, function () {
            return $this->paymentMethodService;
        });

        TestServiceRegister::registerService(PaymentMethodService::class, function () {
            return $this->paymentMethodService;
        });

        $this->connectionService = new ConnectionServiceMock(
            $this->unzerFactory,
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

        TestServiceRegister::registerService(
            InlinePaymentService::class, function () {
            return new InlinePaymentService(
                $this->unzerFactory,
                TestServiceRegister::getService(InlinePaymentStrategyFactory::class),
                TestServiceRegister::getService(PaymentMethodService::class),
                TestServiceRegister::getService(TransactionHistoryService::class),
                TestServiceRegister::getService(InlinePaymentFactory::class),
                TestServiceRegister::getService(CustomerFactory::class),
                TestServiceRegister::getService(MetadataProvider::class),
            );
        },
        );

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

        $this->setMockPaymentMethods();
    }

    public function testInlinePayment()
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'ideal', 'card', 'test']);

        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        $expectedCharge = new Charge(123.23, Currency::getDefault(), 'test.my.shop.com');
        $expectedCharge->setOrderId('test-order-123');
        $expectedInlineResponse = new InlinePaymentResponse(new InlinePayment($expectedCharge));

        $request = new InlinePaymentCreateRequest(
            PaymentMethodTypes::IDEAL,
            'test-order-123',
            Amount::fromFloat(123.23, Currency::getDefault()),
            'test.my.shop.com'
        );

        // Act
        $response = CommonFlowFactory::make('1', $request->getPaymentMethodType())->create($request);

        // Assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('performCharge');
        self::assertNotEmpty($methodCallHistory);
        self::assertTrue($response->isSuccessful());
        self::assertEquals($expectedInlineResponse->getInlinePayment()->getCharge(), $methodCallHistory[0]['charge']);

        self::assertTransactionHistory(
            new TransactionHistory(PaymentMethodTypes::IDEAL, 'test-order-123', 'EUR')
        );
    }

    public function testPaypagePayment()
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'ideal', 'card', 'test']);

        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        $expectedCharge = new Charge(123.23, Currency::getDefault(), 'test.my.shop.com');
        $expectedCharge->setOrderId('test-order-123');
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
        $response = CommonFlowFactory::make('1', $request->getPaymentMethodType())->create($request);

        // Assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('createPaypage');
        self::assertNotEmpty($methodCallHistory);
        self::assertTrue($response->isSuccessful());
        self::assertEquals('test.unzer.api.com', $response->getRedirectUrl());

        self::assertTransactionHistory(
            new TransactionHistory(PaymentMethodTypes::CARDS, 'test-order-123', 'EUR')
        );
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

        $nameIdeal = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Ideal'],
            ['locale' => 'de', 'value' => 'Ideal']
        ]);
        $descriptionIdeal = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Ideal'],
            ['locale' => 'de', 'value' => 'Ideal']
        ]);

        $nameUnknown = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Unknown'],
            ['locale' => 'de', 'value' => 'Unknown']
        ]);
        $descriptionUnknown = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Unknown'],
            ['locale' => 'de', 'value' => 'Unknown']
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
                    TestBookingMethod::test(),
                    true,
                    $nameCard,
                    $descriptionCard,
                    '1',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('us', 'United States')]
                ),
                new PaymentMethodConfig(
                    PaymentMethodTypes::IDEAL,
                    true,
                    BookingMethod::charge(),
                    true,
                    $nameIdeal,
                    $descriptionIdeal,
                    '1',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('us', 'United States')]
                ),
                new PaymentMethodConfig(
                    'unknown',
                    true,
                    BookingMethod::charge(),
                    true,
                    $nameUnknown,
                    $descriptionUnknown,
                    '1',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('us', 'United States')]
                ),
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
}