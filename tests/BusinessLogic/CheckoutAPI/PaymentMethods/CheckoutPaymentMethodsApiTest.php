<?php

namespace Unzer\Core\Tests\BusinessLogic\CheckoutAPI\PaymentMethods;

use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request\PaymentMethodsRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
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
 * Class CheckoutPaymentMethodsApiTest.
 *
 * @package BusinessLogic\CheckoutAPI\PaymentMethods
 */
class CheckoutPaymentMethodsApiTest extends BaseTestCase
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
    public function testGetPaymentMethodsSuccess(): void
    {
        // Arrange
        // Act
        $response = CheckoutAPI::get()->paymentMethods('1')->getAvailablePaymentMethods(
            new PaymentMethodsRequest(
                'DE',
                Amount::fromFloat(1.1, Currency::getDefault()),
                'en')
        );

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     */
    public function testGetPaymentMethodsToArrayEmpty(): void
    {
        // Arrange
        // Act
        $response = CheckoutAPI::get()->paymentMethods('1')->getAvailablePaymentMethods(
            new PaymentMethodsRequest(
                'DE',
                Amount::fromFloat(1.1, Currency::getDefault()),
                'en')
        );

        // Assert
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     */
    public function testGetPaymentMethodsToArray(): void
    {
        // Arrange
        $this->paymentMethodServiceMock->setMockPaymentMethods(
            [
                new PaymentMethodConfig(
                    PaymentMethodTypes::EPS,
                    true,
                    BookingMethod::authorize(),
                    true,
                    [new TranslatableLabel('Eps eng', 'en'), new TranslatableLabel('Eps De', 'de')],
                    [new TranslatableLabel('Eps eng desc', 'en'), new TranslatableLabel('Eps De desc', 'de')],
                    '2',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('DE', 'Germany'), new Country('FR', 'France')]
                )
            ]
        );

        // Act
        $response = CheckoutAPI::get()->paymentMethods('1')->getAvailablePaymentMethods(
            new PaymentMethodsRequest(
                'DE',
                Amount::fromFloat(1.1, Currency::getDefault()),
                'en')
        );

        // Assert
        self::assertEquals([
            [
                'type' => 'EPS',
                'name' => 'Eps eng',
                'description' => 'Eps eng desc',
                'surcharge' => [
                    'value' => 3.3,
                    'currency' => 'EUR'
                ]
            ],
        ],
            $response->toArray()
        );
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     */
    public function testGetPaymentMethodsToArrayDefaultNameAndDescription(): void
    {
        // Arrange
        $this->paymentMethodServiceMock->setMockPaymentMethods(
            [
                new PaymentMethodConfig(
                    PaymentMethodTypes::EPS,
                    true,
                    BookingMethod::authorize(),
                    true,
                    [new TranslatableLabel('Eps eng', 'default'), new TranslatableLabel('Eps De', 'de')],
                    [new TranslatableLabel('Eps eng desc', 'default'), new TranslatableLabel('Eps De desc', 'de')],
                    '2',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('DE', 'Germany'), new Country('FR', 'France')]
                )
            ]
        );

        // Act
        $response = CheckoutAPI::get()->paymentMethods('1')->getAvailablePaymentMethods(
            new PaymentMethodsRequest(
                'DE',
                Amount::fromFloat(1.1, Currency::getDefault()),
                'en')
        );

        // Assert
        self::assertEquals([
            [
                'type' => 'EPS',
                'name' => 'Eps eng',
                'description' => 'Eps eng desc',
                'surcharge' => [
                    'value' => 3.3,
                    'currency' => 'EUR'
                ]
            ],
        ],
            $response->toArray()
        );
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     */
    public function testGetPaymentMethodsToArrayNoDefaultNameAndDescription(): void
    {
        // Arrange
        $this->paymentMethodServiceMock->setMockPaymentMethods(
            [
                new PaymentMethodConfig(
                    PaymentMethodTypes::EPS,
                    true,
                    BookingMethod::authorize(),
                    true,
                    [],
                    [],
                    '2',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('DE', 'Germany'), new Country('FR', 'France')]
                )
            ]
        );

        // Act
        $response = CheckoutAPI::get()->paymentMethods('1')->getAvailablePaymentMethods(
            new PaymentMethodsRequest(
                'DE',
                Amount::fromFloat(1.1, Currency::getDefault()),
                'en')
        );

        // Assert
        self::assertEquals([
            [
                'type' => 'EPS',
                'name' => 'EPS',
                'surcharge' => [
                    'value' => 3.3,
                    'currency' => 'EUR'
                ]
            ],
        ],
            $response->toArray()
        );
    }
}
