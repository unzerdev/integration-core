<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\Transaction;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Transaction\Request\GetTransactionHistoryRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\AuthorizeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\ChargeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\TransactionHistoryServiceMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Constants\TransactionTypes;

/**
 * Class TransactionControllerTest.
 *
 * @package BusinessLogic\AdminAPI\Transaction
 */
class TransactionControllerTest extends BaseTestCase
{
    /**
     * @var TransactionHistoryService
     */
    public TransactionHistoryService $transactionHistoryService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionHistoryService = new TransactionHistoryServiceMock(
            TestServiceRegister::getService(TransactionHistoryRepositoryInterface::class)
        );

        TestServiceRegister::registerService(TransactionHistoryService::class, function () {
            return $this->transactionHistoryService;
        });
    }

    /**
     * @return void
     */
    public function testIsGetResponseSuccessful(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->transaction('1')->getTransactionHistory(new GetTransactionHistoryRequest('1'));

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     */
    public function testToArrayNoTransactionHistory(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->transaction('1')->getTransactionHistory(new GetTransactionHistoryRequest('1'));

        // Assert
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     */
    public function testToArrayWithTransactionHistory(): void
    {
        // Arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            Amount::fromFloat(3.11, Currency::getDefault()),
            [
                new HistoryItem('id1', 'type1', '2024-10-28 09:11:48', Amount::fromFloat(3, Currency::getDefault()),
                    'status1'),
                new AuthorizeHistoryItem('id2', '2024-10-28 09:11:49', Amount::fromFloat(2, Currency::getDefault()),
                    'status2',
                    Amount::fromFloat(1, Currency::getDefault())),
                new ChargeHistoryItem('id3', '2024-10-28 09:11:50', Amount::fromFloat(1, Currency::getDefault()),
                    'status3',
                    Amount::fromFloat(50, Currency::getDefault()))
            ]
        );

        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);
        // Act
        $response = AdminAPI::get()->transaction('1')->getTransactionHistory(new GetTransactionHistoryRequest('order1'));

        // Assert
        self::assertNotEmpty($response->toArray());
        self::assertEquals(PaymentMethodTypes::APPLE_PAY, $response->toArray()['type']);
        self::assertEquals('order1', $response->toArray()['orderId']);
        self::assertEquals('payment1', $response->toArray()['paymentId']);
        self::assertEquals('payment1', $response->toArray()['paymentId']);
        self::assertEquals(11.11, $response->toArray()['amounts']['authorized']['amount']);
        self::assertEquals('EUR', $response->toArray()['amounts']['authorized']['currency']);
        self::assertEquals(1.11, $response->toArray()['amounts']['charged']['amount']);
        self::assertEquals('EUR', $response->toArray()['amounts']['charged']['currency']);
        self::assertEquals(50, $response->toArray()['amounts']['refunded']['amount']);
        self::assertEquals('EUR', $response->toArray()['amounts']['refunded']['currency']);
        self::assertEmpty($response->toArray()['amounts']['cancelled']);
        self::assertEquals('id3', $response->toArray()['items'][0]['id']);
        self::assertEquals('id2', $response->toArray()['items'][1]['id']);
        self::assertEquals('id1', $response->toArray()['items'][2]['id']);
        self::assertEquals(TransactionTypes::CHARGE, $response->toArray()['items'][0]['type']);
        self::assertEquals(TransactionTypes::AUTHORIZATION, $response->toArray()['items'][1]['type']);
        self::assertEquals('type1', $response->toArray()['items'][2]['type']);
        self::assertEquals('2024-10-28 09:11:50', $response->toArray()['items'][0]['date']);
        self::assertEquals('2024-10-28 09:11:49', $response->toArray()['items'][1]['date']);
        self::assertEquals('2024-10-28 09:11:48', $response->toArray()['items'][2]['date']);
        self::assertEquals(1, $response->toArray()['items'][0]['amount']['amount']);
        self::assertEquals(2, $response->toArray()['items'][1]['amount']['amount']);
        self::assertEquals(3, $response->toArray()['items'][2]['amount']['amount']);
        self::assertEquals('EUR', $response->toArray()['items'][0]['amount']['currency']);
        self::assertEquals('EUR', $response->toArray()['items'][1]['amount']['currency']);
        self::assertEquals('EUR', $response->toArray()['items'][2]['amount']['currency']);
    }
}
