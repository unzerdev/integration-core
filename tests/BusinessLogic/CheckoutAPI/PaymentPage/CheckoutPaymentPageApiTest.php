<?php

namespace Unzer\Core\Tests\BusinessLogic\CheckoutAPI\PaymentPage;

use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Services\PaymentPageService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\CurrencyServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\KeypairMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\PaymentMethodServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Resources\PaymentTypes\Paypage;

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

        TestServiceRegister::registerService(PaymentMethodService::class, function () {
            return $this->paymentMethodService;
        });
        TestServiceRegister::registerService(PaymentPageService::class, function () {
            return new PaymentPageService(
                $this->unzerFactory,
                TestServiceRegister::getService(PaymentMethodService::class)
            );
        });

        $this->setMockPaymentMethods();
    }

    public function testAuthorizedPaymentPageWithMinimalRequest()
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);
        $expectedPayPageRequest = new Paypage(123.23, Currency::getDefault(), 'test.my.shop.com');
        $expectedPayPageRequest->setExcludeTypes(['googlepay', 'card', 'test']);

        $expectedResponse = ['id' => 'test-123', 'redirectUrl' => 'test.unzer.api.com'];
        $this->unzerFactory->getMockUnzer()->setPayPageData($expectedResponse);

        $request = new PaymentPageCreateRequest(
            PaymentMethodTypes::EPS,
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
        self::assertEquals($expectedResponse, $response->toArray());
    }

    public function testChargePaymentPageWithMinimalRequest()
    {
        // Arrange
        $this->mockData('s-pub-test', 's-priv-test', ['EPS', 'googlepay', 'card', 'test']);
        $expectedPayPageRequest = new Paypage(123.23, Currency::getDefault(), 'test.my.shop.com');
        $expectedPayPageRequest->setExcludeTypes(['EPS', 'googlepay', 'test']);

        $expectedResponse = ['id' => 'test-123', 'redirectUrl' => 'test.unzer.api.com'];
        $this->unzerFactory->getMockUnzer()->setPayPageData($expectedResponse);

        $request = new PaymentPageCreateRequest(
            PaymentMethodTypes::CARDS,
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
        self::assertEquals($expectedResponse, $response->toArray());
    }

    private function setMockPaymentMethods()
    {
        $this->paymentMethodService->setMockPaymentMethods(
            [
                new PaymentMethodConfig(
                    PaymentMethodTypes::EPS,
                    true,
                    [new TranslatableLabel('Eps eng', 'en'), new TranslatableLabel('Eps De', 'de')],
                    [new TranslatableLabel('Eps eng desc', 'en'), new TranslatableLabel('Eps De desc', 'de')],
                    BookingMethod::authorize(),
                    '2',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('DE', 'Germany'), new Country('FR', 'France')]
                ),
                new PaymentMethodConfig(
                    PaymentMethodTypes::CARDS,
                    true,
                    [new TranslatableLabel('Card', 'eng'), new TranslatableLabel('Card', 'de')],
                    [new TranslatableLabel('Card', 'eng'), new TranslatableLabel('Card', 'de')],
                    BookingMethod::charge(),
                    '1',
                    Amount::fromFloat(1.1, Currency::getDefault()),
                    Amount::fromFloat(2.2, Currency::getDefault()),
                    Amount::fromFloat(3.3, Currency::getDefault()),
                    [new Country('gb', 'Great Britain'), new Country('us', 'United States')],
                    false
                )
            ]
        );
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
