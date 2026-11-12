<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\OrderManagement;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\CancellationRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\ChargeRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\Customer\CustomerData;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\Customer\UpdateCustomerRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\RefundRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Response\ChargeResponse;
use Unzer\Core\BusinessLogic\ApiFacades\Response\ErrorResponse;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\ChargePaymentNotReadableException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\ChargeResult;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\ChargeSkipReason;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Services\OrderManagementService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use UnzerSDK\Resources\TransactionTypes\Charge;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\OrderManagementServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class OrderManagementApiTest.
 *
 * @package BusinessLogic\AdminAPI\OrderManagement
 */
class OrderManagementApiTest extends BaseTestCase
{
    /**
     * @var OrderManagementServiceMock
     */
    private OrderManagementServiceMock $orderManagementService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->orderManagementService = new OrderManagementServiceMock(
            (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test')),
            TestServiceRegister::getService(TransactionHistoryService::class)
        );

        TestServiceRegister::registerService(
            OrderManagementService::class, function () {
            return $this->orderManagementService;
        });
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function testChargeSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->charge(
            new ChargeRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'test-ref')
        );


        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function testChargeToArray(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->charge(
            new ChargeRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'test-ref')
        );

        // Assert
        self::assertEquals(
            ['status' => ChargeResult::SKIPPED, 'skipReason' => ChargeSkipReason::PAYMENT_NOT_FOUND, 'chargeId' => null],
            $response->toArray()
        );
    }

    /**
     * @return void
     */
    public function testChargeResponseCarriesTheChargeResult(): void
    {
        // Arrange
        $charge = (new Charge(1.1, 'EUR'))->setId('s-chg-1');
        $result = ChargeResult::charged($charge, new TransactionHistory('card', 'orderId', 'EUR'));
        $this->orderManagementService->setChargeResult($result);

        // Act
        $response = AdminAPI::get()->order('1')->charge(
            new ChargeRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'test-ref')
        );

        // Assert
        self::assertInstanceOf(ChargeResponse::class, $response);
        self::assertSame($result, $response->getResult());
        self::assertEquals(
            ['status' => ChargeResult::CHARGED, 'skipReason' => null, 'chargeId' => 's-chg-1'],
            $response->toArray()
        );
    }

    /**
     * @return void
     */
    public function testChargePaymentNotReadableBecomesAnErrorResponse(): void
    {
        // Arrange
        $unzerError = new UnzerApiException('Payment not found', 'Payment not found', 'API.310.100.003');
        $this->orderManagementService = new class(
            (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test')),
            TestServiceRegister::getService(TransactionHistoryService::class),
            $unzerError
        ) extends OrderManagementServiceMock {
            private UnzerApiException $error;

            public function __construct($unzerFactory, $transactionHistoryService, UnzerApiException $error)
            {
                parent::__construct($unzerFactory, $transactionHistoryService);
                $this->error = $error;
            }

            public function chargeOrder(string $orderId, ?Amount $chargeAmount, ?string $reference = null): ChargeResult
            {
                throw new ChargePaymentNotReadableException(
                    new TranslatableLabel('Unzer payment could not be read.', 'orderManagement.chargePaymentNotReadable'),
                    $this->error
                );
            }
        };

        // Act
        $response = AdminAPI::get()->order('1')->charge(
            new ChargeRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'test-ref')
        );

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertInstanceOf(ErrorResponse::class, $response);
        self::assertInstanceOf(ChargePaymentNotReadableException::class, $response->getError());
        self::assertSame($unzerError, $response->getError()->getPrevious());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     * @throws CurrencyMismatchException
     * */
    public function testRefundSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->refund(
            new RefundRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'test-ref')
        );

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws CurrencyMismatchException
     * @throws UnzerApiException
     */
    public function testRefundToArray(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->refund(
            new RefundRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'test-ref')
        );

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function testCancellationSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->cancel(
            new CancellationRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'test-ref')
        );

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function testCancellationToArray(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->cancel(
            new CancellationRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault()), 'test-ref')
        );

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function testUpdateCustomerSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->updateCustomer(
            new UpdateCustomerRequest('orderId', 's-cst-123', new CustomerData('John', 'Doe'))
        );

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function testUpdateCustomerToArray(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->updateCustomer(
            new UpdateCustomerRequest('orderId', 's-cst-123', new CustomerData('John', 'Doe'))
        );

        // Assert
        self::assertEquals([], $response->toArray());
    }
}
