<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\OrderManagement;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\CancellationRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\ChargeRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\RefundRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\CancellationNotPossibleException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\ChargeNotPossibleException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\RefundNotPossibleException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Services\OrderManagementService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidTransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
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
     * @var OrderManagementService
     */
    private OrderManagementService $orderManagementService;

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
     * @throws ChargeNotPossibleException
     * @throws InvalidTransactionHistory
     * @throws TransactionHistoryNotFoundException
     */
    public function testChargeSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->charge(
            new ChargeRequest('orderId',
                Amount::fromFloat(1.1, Currency::getDefault())
            )
        );


        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws ChargeNotPossibleException
     * @throws InvalidTransactionHistory
     * @throws TransactionHistoryNotFoundException
     */
    public function testChargeToArray(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->charge(
            new ChargeRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault())
            )
        );

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidTransactionHistory
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     * @throws CurrencyMismatchException
     * @throws RefundNotPossibleException
     */
    public function testRefundSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->refund(
            new RefundRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault())
            )
        );

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws CurrencyMismatchException
     * @throws InvalidTransactionHistory
     * @throws RefundNotPossibleException
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function testRefundToArray(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->refund(
            new RefundRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault()))
        );

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidTransactionHistory
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     * @throws CancellationNotPossibleException
     */
    public function testCancellationSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->cancel(
            new CancellationRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault())
            )
        );

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws CancellationNotPossibleException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidTransactionHistory
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function testCancellationToArray(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->order('1')->cancel(
            new CancellationRequest('orderId', Amount::fromFloat(1.1, Currency::getDefault())
            )
        );

        // Assert
        self::assertEquals([], $response->toArray());
    }
}
