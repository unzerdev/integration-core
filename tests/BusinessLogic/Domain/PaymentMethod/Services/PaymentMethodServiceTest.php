<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\PaymentMethod\Services;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig as PaymentMethodConfigEntity;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\Integration\Currency\CurrencyServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodNames;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\CurrencyServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\KeypairMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentMethodServiceTest.
 *
 * @package BusinessLogic\Domain\PaymentMethod\Services
 */
class PaymentMethodServiceTest extends BaseTestCase
{
    /**
     * @var PaymentMethodService
     */
    public $service;

    /**
     * @var CurrencyServiceMock
     */
    public CurrencyServiceMock $currencyServiceMock;

    /**
     * @var MemoryRepository
     */
    public $paymentMethodConfigRepository;
    private UnzerFactoryMock $unzerFactory;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->unzerFactory = (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test'));
        TestServiceRegister::registerService(PaymentMethodService::class, function () {
            return new PaymentMethodService(
                $this->unzerFactory,
                TestServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class),
                TestServiceRegister::getService(CurrencyServiceInterface::class),
            );
        });

        $this->paymentMethodConfigRepository = TestRepositoryRegistry::getRepository(PaymentMethodConfigEntity::getClassName());
        $this->service = TestServiceRegister::getService(PaymentMethodService::class);
        $this->currencyServiceMock = TestServiceRegister::getService(CurrencyServiceInterface::class);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testNoPaymentMethods(): void
    {
        // arrange
        $this->mockData('s-pub-test', 's-priv-test');

        // act
        $methods = StoreContext::doWithStore('1', [$this->service, 'getAllPaymentMethods']);

        // assert
        self::assertEmpty($methods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsNoConfigs(): void
    {
        // arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);

        // act
        $methods = StoreContext::doWithStore('1', [$this->service, 'getAllPaymentMethods']);

        // assert

        $expectedMethods = [
            new PaymentMethod('EPS', PaymentMethodNames::PAYMENT_METHOD_NAMES['EPS'], false),
            new PaymentMethod('googlepay', PaymentMethodNames::PAYMENT_METHOD_NAMES['googlepay'], false),
            new PaymentMethod('card', PaymentMethodNames::PAYMENT_METHOD_NAMES['card'], false),
            new PaymentMethod('test', PaymentMethodNames::DEFAULT_PAYMENT_METHOD_NAME . ' test', false),
        ];

        self::assertCount(4, $methods);
        self::assertEquals($expectedMethods, $methods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethods(): void
    {
        // arrange
        $this->setEntities(
            [
                new PaymentMethodConfig('przelewy24', true, BookingMethod::charge()),
                new PaymentMethodConfig('giropay', false, BookingMethod::charge()),
                new PaymentMethodConfig('twint', true, BookingMethod::charge()),
                new PaymentMethodConfig('wechatpay', true, BookingMethod::charge()),
            ]
        );

        $this->mockData('s-pub-test', 's-priv-test', ['przelewy24', 'giropay', 'twint', 'wechatpay']);

        // act
        $methods = StoreContext::doWithStore('1', [$this->service, 'getAllPaymentMethods']);

        // assert
        $expectedMethods = [
            new PaymentMethod('przelewy24', PaymentMethodNames::PAYMENT_METHOD_NAMES['przelewy24'], true),
            new PaymentMethod('twint', PaymentMethodNames::PAYMENT_METHOD_NAMES['twint'], true),
            new PaymentMethod('wechatpay', PaymentMethodNames::PAYMENT_METHOD_NAMES['wechatpay'], true),
        ];

        self::assertEquals($expectedMethods, $methods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsConfigAndAPI(): void
    {
        // arrange
        $this->setEntities(
            [
                new PaymentMethodConfig('prepayment', true, BookingMethod::charge()),
                new PaymentMethodConfig('post-finance-card', false, BookingMethod::charge()),
                new PaymentMethodConfig('paylater-direct-debit', false, BookingMethod::charge()),
                new PaymentMethodConfig('bancontact', true, BookingMethod::charge()),
            ]
        );

        $this->mockData('s-pub-test',
            's-priv-test',
            ['prepayment', 'payu', 'sepa-direct-debit', 'paylater-direct-debit', 'paylater-installment']);

        // act
        $methods = StoreContext::doWithStore('1', [$this->service, 'getAllPaymentMethods']);

        // assert
        $expectedMethods = [
            new PaymentMethod('prepayment', PaymentMethodNames::PAYMENT_METHOD_NAMES['prepayment'], true),
            new PaymentMethod('payu', PaymentMethodNames::PAYMENT_METHOD_NAMES['payu'], false),
            new PaymentMethod('sepa-direct-debit', PaymentMethodNames::PAYMENT_METHOD_NAMES['sepa-direct-debit'],
                false),
            new PaymentMethod('paylater-direct-debit',
                PaymentMethodNames::PAYMENT_METHOD_NAMES['paylater-direct-debit'], false),
            new PaymentMethod('paylater-installment',
                PaymentMethodNames::PAYMENT_METHOD_NAMES['paylater-installment'], false),
        ];

        self::assertEquals($expectedMethods, $methods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testEnablePaymentConfig(): void
    {
        // arrange
        $paymentMethodConfig = new PaymentMethodConfig(PaymentMethodTypes::CARDS, true, BookingMethod::charge());

        // act
        StoreContext::doWithStore(
            '1',
            [$this->service, 'enablePaymentMethodConfig'],
            [PaymentMethodTypes::CARDS, true]
        );

        // assert
        $savedEntity = $this->paymentMethodConfigRepository->selectOne();

        self::assertEquals($paymentMethodConfig, $savedEntity->getPaymentMethodConfig());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testEnablePaymentConfigAuthorizeType(): void
    {
        // arrange
        $paymentMethodConfig = new PaymentMethodConfig(
            PaymentMethodTypes::KLARNA,
            true,
            BookingMethod::authorize(),
            true
        );

        // act
        StoreContext::doWithStore(
            '1',
            [$this->service, 'enablePaymentMethodConfig'],
            [PaymentMethodTypes::KLARNA, true]
        );

        // assert
        $savedEntity = $this->paymentMethodConfigRepository->selectOne();

        self::assertEquals($paymentMethodConfig, $savedEntity->getPaymentMethodConfig());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentConfig(): void
    {
        // arrange
        $paymentMethodConfig = new PaymentMethodConfig(PaymentMethodTypes::KLARNA, true, BookingMethod::charge());
        $this->setEntities([$paymentMethodConfig]);

        // act
        $config = StoreContext::doWithStore('1', [$this->service, 'getPaymentMethodConfigByType'], ['klarna']);

        // assert
        self::assertEquals($paymentMethodConfig, $config);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSavePaymentMethodConfig(): void
    {
        // arrange
        $paymentMethodConfig = new PaymentMethodConfig(PaymentMethodTypes::KLARNA, true, BookingMethod::charge());
        $this->setEntities([$paymentMethodConfig]);

        // act
        StoreContext::doWithStore('1', [$this->service, 'savePaymentMethodConfig'], [$paymentMethodConfig]);

        // assert
        $savedEntity = $this->paymentMethodConfigRepository->selectOne();

        self::assertEquals($paymentMethodConfig, $savedEntity->getPaymentMethodConfig());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsForCheckoutEmptyNoConfig(): void
    {
        // arrange

        // act
        $paymentMethods = StoreContext::doWithStore(
            '1',
            [$this->service, 'getPaymentMethodsForCheckout'],
            [Amount::fromInt(1, Currency::getDefault()), 'en']
        );

        // assert
        self::assertEmpty($paymentMethods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsForCheckoutEmptyDisabledConfigs(): void
    {
        // arrange
        $this->setEntities(
            [
                new PaymentMethodConfig('prepayment', false, BookingMethod::charge()),
                new PaymentMethodConfig('post-finance-card', false, BookingMethod::charge()),
                new PaymentMethodConfig('paylater-direct-debit', false, BookingMethod::charge()),
                new PaymentMethodConfig('bancontact', false, BookingMethod::charge()),
            ]
        );

        // act
        $paymentMethods = StoreContext::doWithStore(
            '1',
            [$this->service, 'getPaymentMethodsForCheckout'],
            [Amount::fromInt(1, Currency::getDefault()), 'en']
        );

        // assert
        self::assertEmpty($paymentMethods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsForCheckoutEmptyRestrictedCountries(): void
    {
        // arrange
        $nameEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);
        $descriptionEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps description test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);

        $nameCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $descriptionCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $this->setEntities(
            [
                new PaymentMethodConfig(
                    PaymentMethodTypes::EPS,
                    true,
                    BookingMethod::charge(),
                    false,
                    $nameEps,
                    $descriptionEps,
                    '1',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('fr', 'France')]
                ),
                new PaymentMethodConfig(
                    PaymentMethodTypes::CARDS,
                    true,
                    BookingMethod::charge(),
                    false,
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

        // act
        $paymentMethods = StoreContext::doWithStore(
            '1',
            [$this->service, 'getPaymentMethodsForCheckout'],
            [Amount::fromInt(12, Currency::fromIsoCode('USD')), 'gb']
        );

        // assert
        self::assertEmpty($paymentMethods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsForCheckoutEmptyMinOrderAmount(): void
    {
        // arrange
        $nameEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);
        $descriptionEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps description test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);

        $nameCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $descriptionCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $this->setEntities(
            [
                new PaymentMethodConfig(
                    PaymentMethodTypes::EPS,
                    true,
                    BookingMethod::charge(),
                    false,
                    $nameEps,
                    $descriptionEps,
                    '1',
                    Amount::fromFloat(2, Currency::getDefault()),
                    Amount::fromFloat(3, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('fr', 'France')]
                ),
                new PaymentMethodConfig(
                    PaymentMethodTypes::CARDS,
                    true,
                    BookingMethod::charge(),
                    false,
                    $nameCard,
                    $descriptionCard,
                    '1',
                    Amount::fromFloat(4, Currency::getDefault()),
                    Amount::fromFloat(5, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('us', 'United States')]

                )
            ]
        );

        // act
        $paymentMethods = StoreContext::doWithStore(
            '1',
            [$this->service, 'getPaymentMethodsForCheckout'],
            [Amount::fromInt(1, Currency::getDefault()), 'ch']
        );

        // assert
        self::assertEmpty($paymentMethods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsForCheckoutEmptyMaxOrderAmount(): void
    {
        // arrange
        $nameEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);
        $descriptionEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps description test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);

        $nameCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $descriptionCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $this->setEntities(
            [
                new PaymentMethodConfig(
                    PaymentMethodTypes::EPS,
                    true,
                    BookingMethod::charge(),
                    false,
                    $nameEps,
                    $descriptionEps,
                    '1',
                    Amount::fromFloat(2, Currency::getDefault()),
                    Amount::fromFloat(3, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('fr', 'France')]
                ),
                new PaymentMethodConfig(
                    PaymentMethodTypes::CARDS,
                    true,
                    BookingMethod::charge(),
                    false,
                    $nameCard,
                    $descriptionCard,
                    '1',
                    Amount::fromFloat(4, Currency::getDefault()),
                    Amount::fromFloat(5, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('us', 'United States')]
                )
            ]
        );

        // act
        $paymentMethods = StoreContext::doWithStore(
            '1',
            [$this->service, 'getPaymentMethodsForCheckout'],
            [Amount::fromInt(1212121212, Currency::getDefault()), 'ch']
        );

        // assert
        self::assertEmpty($paymentMethods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsForCheckoutInvalidUnzerAPIResponse(): void
    {
        // arrange
        $nameEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);
        $descriptionEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps description test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);

        $nameCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $descriptionCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $method1 = new PaymentMethodConfig(
            PaymentMethodTypes::EPS,
            true,
            BookingMethod::charge(),
            false,
            $nameEps,
            $descriptionEps,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(30, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('gb', 'Great Britain'), new Country('fr', 'France')]
        );
        $method2 = new PaymentMethodConfig(
            PaymentMethodTypes::CARDS,
            true,
            BookingMethod::charge(),
            false,
            $nameCard,
            $descriptionCard,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(222, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('gb', 'Great Britain'), new Country('us', 'United States')]
        );
        $method3 = new PaymentMethodConfig(
            PaymentMethodTypes::PRZELEWY24,
            false,
            BookingMethod::charge(),
            false,
            $nameCard,
            $descriptionCard,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(222, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('gb', 'Great Britain'), new Country('us', 'United States')]
        );

        $method4 = new PaymentMethodConfig(
            PaymentMethodTypes::PRZELEWY24,
            false,
            BookingMethod::charge(),
            false,
            $nameCard,
            $descriptionCard,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(222, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('ch', 'Switzerland'), new Country('us', 'United States')]
        );
        $this->setEntities([$method1, $method2, $method3, $method4]);
        $this->mockData('s-pub-test', 's-priv-test', [], [
            (object)[
                "type" => "przelewy24",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ]
        ]);

        // act
        $paymentMethods = StoreContext::doWithStore(
            '1',
            [$this->service, 'getPaymentMethodsForCheckout'],
            [Amount::fromInt(200, Currency::fromIsoCode('EUR')), 'ch']
        );

        // assert
        self::assertEmpty($paymentMethods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsForCheckoutInvalidCurrency(): void
    {
        // arrange
        $nameEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);
        $descriptionEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps description test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);

        $method1 = new PaymentMethodConfig(
            PaymentMethodTypes::EPS,
            true,
            BookingMethod::charge(),
            false,
            $nameEps,
            $descriptionEps,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(30, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('gb', 'Great Britain'), new Country('fr', 'France')]

        );

        $this->setEntities([$method1]);
        $this->mockData('s-pub-test', 's-priv-test');

        // act
        $paymentMethods = StoreContext::doWithStore(
            '1',
            [$this->service, 'getPaymentMethodsForCheckout'],
            [Amount::fromInt(200, Currency::fromIsoCode('USD')), 'ch']
        );

        // assert
        self::assertEmpty($paymentMethods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsForCheckoutInvalidCountry(): void
    {
        // arrange
        $nameEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);
        $descriptionEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps description test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);
        $method1 = new PaymentMethodConfig(
            PaymentMethodTypes::EPS,
            true,
            BookingMethod::charge(),
            false,
            $nameEps,
            $descriptionEps,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(30, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('gb', 'Great Britain'), new Country('fr', 'France')]
        );

        $this->setEntities([$method1]);
        $this->mockData('s-pub-test', 's-priv-test', [], [
            (object)[
                "supports" => [
                    (object)[
                        "brands" => ["PRZELEWY24"],
                        "countries" => ['FR'],
                        "channel" => "31HA07BC81AE5E9FBF7C1A4AE013EA94",
                        "currency" => ["EUR"]
                    ]
                ],
                "type" => "EPS",
                "allowCustomerTypes" => "B2C",
                "allowCreditTransaction" => false,
                "3ds" => false
            ]
        ]);

        // act
        $paymentMethods = StoreContext::doWithStore(
            '1',
            [$this->service, 'getPaymentMethodsForCheckout'],
            [Amount::fromInt(200, Currency::fromIsoCode('EUR')), 'ch']
        );

        // assert
        self::assertEmpty($paymentMethods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsForCheckout(): void
    {
        // arrange
        $nameEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);
        $descriptionEps = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Eps description test'],
            ['locale' => 'de', 'value' => 'Eps 2 test']
        ]);

        $nameCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $descriptionCard = TranslationCollection::fromArray([
            ['locale' => 'en', 'value' => 'Card'],
            ['locale' => 'de', 'value' => 'Card']
        ]);
        $method1 = new PaymentMethodConfig(
            PaymentMethodTypes::EPS,
            true,
            BookingMethod::charge(),
            false,
            $nameEps,
            $descriptionEps,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(30, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('gb', 'Great Britain'), new Country('fr', 'France')]
        );
        $method2 = new PaymentMethodConfig(
            PaymentMethodTypes::CARDS,
            true,
            BookingMethod::charge(),
            false,
            $nameCard,
            $descriptionCard,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(222, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('gb', 'Great Britain'), new Country('us', 'United States')]
        );
        $method3 = new PaymentMethodConfig(
            PaymentMethodTypes::PRZELEWY24,
            false,
            BookingMethod::charge(),
            false,
            $nameCard,
            $descriptionCard,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(222, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('gb', 'Great Britain'), new Country('us', 'United States')]
        );

        $method4 = new PaymentMethodConfig(
            PaymentMethodTypes::PRZELEWY24,
            false,
            BookingMethod::charge(),
            false,
            $nameCard,
            $descriptionCard,
            '1',
            Amount::fromFloat(1, Currency::getDefault()),
            Amount::fromFloat(222, Currency::getDefault()),
            Amount::fromFloat(3.3, Currency::getDefault()),
            [new Country('ch', 'Switzerland'), new Country('us', 'United States')]
        );
        $this->setEntities([$method1, $method2, $method3, $method4]);
        $this->mockData('s-pub-test', 's-priv-test');

        // act
        $paymentMethods = StoreContext::doWithStore(
            '1',
            [$this->service, 'getPaymentMethodsForCheckout'],
            [Amount::fromInt(200, Currency::fromIsoCode('EUR')), 'gb']
        );

        // assert
        self::assertEquals([$method1, $method2], $paymentMethods);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsExcludeUnsupported(): void
    {
        // arrange
        $this->setEntities(
            [
                new PaymentMethodConfig('przelewy24', true, BookingMethod::charge()),
                new PaymentMethodConfig('giropay', false, BookingMethod::charge()),
                new PaymentMethodConfig('twint', true, BookingMethod::charge()),
                new PaymentMethodConfig('wechatpay', true, BookingMethod::charge()),
                new PaymentMethodConfig('applepay', true, BookingMethod::charge())
            ]
        );

        $this->mockData('s-pub-test', 's-priv-test', ['przelewy24', 'giropay', 'twint', 'wechatpay', 'invoice-secured']);

        // act
        $methods = StoreContext::doWithStore('1', [$this->service, 'getAllPaymentMethods']);

        // assert
        $expectedMethods = [
            new PaymentMethod('przelewy24', PaymentMethodNames::PAYMENT_METHOD_NAMES['przelewy24'], true),
            new PaymentMethod('twint', PaymentMethodNames::PAYMENT_METHOD_NAMES['twint'], true),
            new PaymentMethod('wechatpay', PaymentMethodNames::PAYMENT_METHOD_NAMES['wechatpay'], true),
        ];

        self::assertEquals($expectedMethods, $methods);
    }


    /**
     * @param PaymentMethodConfig[] $paymentMethods
     *
     * @return void
     */
    private function setEntities(array $paymentMethods)
    {
        foreach ($paymentMethods as $method) {
            $entity = new PaymentMethodConfigEntity();
            $entity->setPaymentMethodConfig($method);
            $entity->setStoreId('1');
            $entity->setType($method->getType());
            $this->paymentMethodConfigRepository->save($entity);
        }
    }

    /**
     * @return void
     */
    private function mockData(string $publicKey, string $privateKey, array $types = [], array $paymentTypes = [])
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
