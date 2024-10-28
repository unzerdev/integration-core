<?php

namespace Unzer\Core\Tests\BusinessLogic\CheckoutAPI\PaymentPage;

use Unzer\Core\BusinessLogic\ApiFacades\Response\ErrorResponse;
use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\MetadataProvider;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory\BasketFactory;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory\CustomerFactory;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory\PaymentPageFactory;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Services\PaymentPageService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\CurrencyServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\KeypairMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentMethodServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentSDK;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Resources\PaymentTypes\Paypage;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState as DomainPaymentState;

/**
 * Class CheckoutPaymentPageAPITest
 *
 * @package BusinessLogic\CheckoutAPI\PaymentPage
 */
class CheckoutPaymentPageApiTest extends BaseTestCase
{
    private ?UnzerFactoryMock $unzerFactory;
    private PaymentMethodServiceMock $paymentMethodService;

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

        $this->setMockPaymentMethods();
    }

    public function testMissingPaymentMethodTypeRequest(): void
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);

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
        $expectedPayPageRequest = new Paypage(123.23, Currency::getDefault(), 'test.my.shop.com');
        $expectedPayPageRequest
            ->setExcludeTypes(['googlepay', 'card', 'test'])
            ->setOrderId('test-order-123');

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
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('initPayPageAuthorize');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals($expectedPayPageRequest, $methodCallHistory[0]['paypage']);
        self::assertTrue($response->isSuccessful());
        self::assertNotEmpty($response->toArray());
        self::assertEquals(['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com'], $response->toArray());
        self::assertTransactionHistory(
            new TransactionHistory(PaymentMethodTypes::EPS, 'test-payment-123', 'test-order-123')
        );
    }

    public function testChargePaymentPageWithMinimalRequest(): void
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);
        $expectedPayPageRequest = new Paypage(123.23, Currency::getDefault(), 'test.my.shop.com');
        $expectedPayPageRequest
            ->setExcludeTypes(['EPS', 'googlepay', 'test'])
            ->setOrderId('test-order-123');

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
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('initPayPageCharge');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals($expectedPayPageRequest, $methodCallHistory[0]['paypage']);
        self::assertTrue($response->isSuccessful());
        self::assertNotEmpty($response->toArray());
        self::assertEquals(['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com'], $response->toArray());
        self::assertTransactionHistory(
            new TransactionHistory(PaymentMethodTypes::CARDS, 'test-payment-123', 'test-order-123')
        );
    }

    public function testCheckForUnknownPaymentStatus(): void
    {
        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->getPaymentState('test-order-123');

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertNotEmpty($response->toArray());
        self::assertStringContainsString('history for orderId: test-order-123 not found',
            $response->toArray()['errorMessage']);
        self::assertEquals('transactionHistory.notFound', $response->toArray()['errorCode']);
    }

    public function testCheckSuccessfulPaymentStatus(): void
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);
//        $expectedPayPageRequest = new Paypage(123.23, Currency::getDefault(), 'test.my.shop.com');
//        $expectedPayPageRequest
//            ->setExcludeTypes(['EPS', 'googlepay', 'test'])
//            ->setOrderId('test-order-123');

        $this->unzerFactory->getMockUnzer()
            ->setPayPageData(
                ['id' => 'test-paypage-123', 'redirectUrl' => 'test.unzer.api.com', 'paymentId' => 'test-payment-123']
            )->setPayment((new PaymentSDK())->setState(PaymentState::STATE_COMPLETED));

        CheckoutAPI::get()->paymentPage('1')->create(new PaymentPageCreateRequest(
            PaymentMethodTypes::CARDS,
            'test-order-123',
            Amount::fromFloat(123.23, Currency::getDefault()),
            'test.my.shop.com'
        ));

        // Act
        $response = CheckoutAPI::get()->paymentPage('1')->getPaymentState('test-order-123');

        // Assert
        $methodCallHistory = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('fetchPayment');
        self::assertNotEmpty($methodCallHistory);
        self::assertEquals('test-payment-123', $methodCallHistory[0]['paymentId']);
        self::assertTrue($response->isSuccessful());
        self::assertEquals(
            ['id' => PaymentState::STATE_COMPLETED, 'name' => PaymentState::STATE_NAME_COMPLETED], $response->toArray()
        );
        self::assertEquals(
            new DomainPaymentState(PaymentState::STATE_COMPLETED,
            PaymentState::STATE_NAME_COMPLETED), $response->getPaymentState()
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

    private function setMockPaymentMethods(): void
    {
        $this->paymentMethodService->setMockPaymentMethods(
            [
                new PaymentMethodConfig(
                    PaymentMethodTypes::EPS,
                    true,
                    BookingMethod::authorize(),
                    false,
                    [new TranslatableLabel('Eps eng', 'en'), new TranslatableLabel('Eps De', 'de')],
                    [new TranslatableLabel('Eps eng desc', 'en'), new TranslatableLabel('Eps De desc', 'de')],
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
                    false,
                    [new TranslatableLabel('Card', 'eng'), new TranslatableLabel('Card', 'de')],
                    [new TranslatableLabel('Card', 'eng'), new TranslatableLabel('Card', 'de')],
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
}
