<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\PaymentMethods;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\EnablePaymentMethodRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\GetPaymentMethodConfigRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\SavePaymentMethodConfigRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Country\Exceptions\InvalidCountryArrayException;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodNames;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidBookingMethodException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidPaymentTypeException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\CurrencyServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentMethodServiceMock as IntegrationMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class PaymentMethodsApiTest.
 *
 * @package BusinessLogic\AdminAPI\PaymentMethods
 */
class PaymentMethodsApiTest extends BaseTestCase
{
    /**
     * @var IntegrationMock
     */
    private IntegrationMock $paymentMethodServiceMock;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->paymentMethodServiceMock = new IntegrationMock(
            (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test')),
            TestServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class),
            new CurrencyServiceMock()
        );

        TestServiceRegister::registerService(
            PaymentMethodService::class, function () {
            return $this->paymentMethodServiceMock;
        });
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     */
    public function testGetAllPaymentMethodsSuccess(): void
    {
        // Arrange
        // Act
        $response = AdminAPI::get()->paymentMethods('1')->getPaymentMethods();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     */
    public function testGetAllPaymentMethodsToArray(): void
    {
        // Arrange
        $this->paymentMethodServiceMock->setMockPaymentMethods([
                new PaymentMethod('prepayment', PaymentMethodNames::PAYMENT_METHOD_NAMES['prepayment'], true),
                new PaymentMethod('payu', PaymentMethodNames::PAYMENT_METHOD_NAMES['payu'], false),
                new PaymentMethod('sepa-direct-debit', PaymentMethodNames::PAYMENT_METHOD_NAMES['sepa-direct-debit'],
                    false),
            ]
        );

        // Act
        $response = AdminAPI::get()->paymentMethods('1')->getPaymentMethods();

        // Assert
        self::assertEquals([
            [
                'type' => 'prepayment',
                'name' => 'Unzer Prepayment',
                'description' => '',
                'enabled' => true,
            ],
            [
                'type' => 'payu',
                'name' => 'PayU',
                'description' => '',
                'enabled' => false,
            ],
            [
                'type' => 'sepa-direct-debit',
                'name' => 'Unzer Direct Debit',
                'description' => '',
                'enabled' => false,
            ]
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidPaymentTypeException
     */
    public function testEnablePaymentMethodSuccess(): void
    {
        // Arrange
        $request = new EnablePaymentMethodRequest('EPS', true);

        // Act
        $response = AdminAPI::get()->paymentMethods('1')->enablePaymentMethod($request);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws InvalidPaymentTypeException
     */
    public function testEnablePaymentMethodToArray(): void
    {
        // Arrange
        $request = new EnablePaymentMethodRequest('EPS', true);

        // Act
        $response = AdminAPI::get()->paymentMethods('1')->enablePaymentMethod($request);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidPaymentTypeException
     * @throws PaymentConfigNotFoundException
     */
    public function testGetPaymentMethodSuccess(): void
    {
        // Arrange
        $request = new GetPaymentMethodConfigRequest(PaymentMethodTypes::EPS);
        $this->paymentMethodServiceMock->setMockPaymentMethod(
            new PaymentMethodConfig(PaymentMethodTypes::EPS, true)
        );

        // Act
        $response = AdminAPI::get()->paymentMethods('1')->getPaymentConfig($request);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws InvalidPaymentTypeException
     * @throws PaymentConfigNotFoundException
     */
    public function testGetPaymentMethodEpsToArray(): void
    {
        // Arrange
        $request = new GetPaymentMethodConfigRequest(PaymentMethodTypes::EPS);
        $this->paymentMethodServiceMock->setMockPaymentMethod(
            new PaymentMethodConfig(
                PaymentMethodTypes::EPS,
                true,
                [new TranslatableLabel('Eps eng', 'en'), new TranslatableLabel('Eps De', 'de')],
                [],
                BookingMethod::authorize(),
                '2',
                Amount::fromFloat(1.1, Currency::getDefault()),
                Amount::fromFloat(2.2, Currency::getDefault()),
                Amount::fromFloat(3.3, Currency::getDefault()),
                [new Country('DE', 'Germany'), new Country('FR', 'France')]
            )
        );

        // Act
        $response = AdminAPI::get()->paymentMethods('1')->getPaymentConfig($request);

        // Assert
        self::assertEquals([
            'type' => PaymentMethodTypes::EPS,
            'typeName' => PaymentMethodNames::PAYMENT_METHOD_NAMES[PaymentMethodTypes::EPS],
            'bookingAvailable' => false,
            'bookingMethod' => 'authorize',
            'name' => [['locale' => 'en', 'value' => 'Eps eng'], ['locale' => 'de', 'value' => 'Eps De']],
            'description' => [],
            'chargeAvailable' => true,
            'minOrderAmount' => 1.1,
            'maxOrderAmount' => 2.2,
            'surcharge' => 3.3,
            'restrictedCountries' => [['code' => 'DE', 'name' => 'Germany'], ['code' => 'FR', 'name' => 'France']],
            'displaySendBasketData' => true,
            'sendBasketData' => false,
            'statusIdToCharge' => '2'
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidPaymentTypeException
     * @throws PaymentConfigNotFoundException
     */
    public function testGetPaymentMethodApplePayToArray(): void
    {
        // Arrange
        $request = new GetPaymentMethodConfigRequest(PaymentMethodTypes::APPLE_PAY);
        $this->paymentMethodServiceMock->setMockPaymentMethod(
            new PaymentMethodConfig(
                PaymentMethodTypes::APPLE_PAY,
                true,
                [new TranslatableLabel('Apple pay eng', 'en'), new TranslatableLabel('Apple pay De', 'de')],
                [],
                BookingMethod::charge(),
                '2',
                Amount::fromFloat(1.1, Currency::getDefault()),
                Amount::fromFloat(2.2, Currency::getDefault()),
                Amount::fromFloat(3.3, Currency::getDefault()),
                [new Country('DE', 'Germany'), new Country('FR', 'France')]
            )
        );

        // Act
        $response = AdminAPI::get()->paymentMethods('1')->getPaymentConfig($request);

        // Assert
        self::assertEquals([
            'type' => PaymentMethodTypes::APPLE_PAY,
            'typeName' => PaymentMethodNames::PAYMENT_METHOD_NAMES[PaymentMethodTypes::APPLE_PAY],
            'bookingAvailable' => true,
            'bookingMethod' => 'charge',
            'name' => [['locale' => 'en', 'value' => 'Apple pay eng'], ['locale' => 'de', 'value' => 'Apple pay De']],
            'description' => [],
            'chargeAvailable' => true,
            'minOrderAmount' => 1.1,
            'maxOrderAmount' => 2.2,
            'surcharge' => 3.3,
            'restrictedCountries' => [['code' => 'DE', 'name' => 'Germany'], ['code' => 'FR', 'name' => 'France']],
            'displaySendBasketData' => true,
            'sendBasketData' => false,
            'statusIdToCharge' => '2'
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidPaymentTypeException
     * @throws PaymentConfigNotFoundException
     */
    public function testGetPaymentMethodKlarnaToArray(): void
    {
        // Arrange
        $request = new GetPaymentMethodConfigRequest(PaymentMethodTypes::KLARNA);
        $this->paymentMethodServiceMock->setMockPaymentMethod(
            new PaymentMethodConfig(
                PaymentMethodTypes::KLARNA,
                true,
                [new TranslatableLabel('KLARNA eng', 'en'), new TranslatableLabel('KLARNA De', 'de')],
                [],
                BookingMethod::authorize(),
                '2',
                Amount::fromFloat(1.1, Currency::getDefault()),
                Amount::fromFloat(2.2, Currency::getDefault()),
                Amount::fromFloat(3.3, Currency::getDefault()),
                [new Country('DE', 'Germany'), new Country('FR', 'France')]
            )
        );

        // Act
        $response = AdminAPI::get()->paymentMethods('1')->getPaymentConfig($request);

        // Assert
        self::assertEquals([
            'type' => PaymentMethodTypes::KLARNA,
            'typeName' => PaymentMethodNames::PAYMENT_METHOD_NAMES[PaymentMethodTypes::KLARNA],
            'bookingAvailable' => false,
            'bookingMethod' => 'authorize',
            'name' => [['locale' => 'en', 'value' => 'KLARNA eng'], ['locale' => 'de', 'value' => 'KLARNA De']],
            'description' => [],
            'chargeAvailable' => false,
            'minOrderAmount' => 1.1,
            'maxOrderAmount' => 2.2,
            'surcharge' => 3.3,
            'restrictedCountries' => [['code' => 'DE', 'name' => 'Germany'], ['code' => 'FR', 'name' => 'France']],
            'displaySendBasketData' => false,
            'sendBasketData' => false,
            'statusIdToCharge' => '2'
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidTranslatableArrayException
     * @throws InvalidCountryArrayException
     * @throws InvalidBookingMethodException
     */
    public function testSavePaymentMethodConfigSuccess(): void
    {
        // Arrange
        $request = new SavePaymentMethodConfigRequest('eps', [], []);

        // Act
        $response = AdminAPI::get()->paymentMethods('1')->savePaymentConfig($request);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws InvalidTranslatableArrayException
     * @throws InvalidCountryArrayException
     * @throws InvalidBookingMethodException
     */
    public function testSavePaymentMethodConfigToArray(): void
    {
        // Arrange
        $request = new SavePaymentMethodConfigRequest(
            'eps',
            [['locale' => 'en', 'value' => 'KLARNA eng'], ['locale' => 'de', 'value' => 'KLARNA De']],
            [],
            null,
            null,
            null,
            null,
            null,
            [['code' => 'fr', 'name' => 'France'], ['code' => 'de', 'name' => 'Germany']]
        );

        // Act
        $response = AdminAPI::get()->paymentMethods('1')->savePaymentConfig($request);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidTranslatableArrayException
     * @throws InvalidCountryArrayException
     * @throws InvalidBookingMethodException
     */
    public function testSavePaymentMethodConfigInvalidTranslatableLabel(): void
    {
        // Arrange
        $request = new SavePaymentMethodConfigRequest('eps', ['test', ''], []);
        $this->expectException(InvalidTranslatableArrayException::class);

        // Act
        $request->toDomainModel(Currency::getDefault());

        // Assert
    }

    /**
     * @return void
     *
     * @throws InvalidTranslatableArrayException
     * @throws InvalidCountryArrayException
     * @throws InvalidBookingMethodException
     */
    public function testSavePaymentMethodConfigInvalidCountryArray(): void
    {
        // Arrange
        $request = new SavePaymentMethodConfigRequest('eps', [], [], null, null, null, null, null, ['test', '']);
        $this->expectException(InvalidCountryArrayException::class);

        // Act
        $request->toDomainModel(Currency::getDefault());
        // Assert
    }
}
