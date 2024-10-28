<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\TransactionHistory\Models;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\AuthorizedItemNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\AuthorizeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\ChargeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\SdkAmount;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use UnzerSDK\Constants\TransactionTypes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Chargeback;
use UnzerSDK\Resources\TransactionTypes\Payout;
use UnzerSDK\Resources\TransactionTypes\Shipment;

/**
 * Class TransactionHistoryModelTest.
 *
 * @package BusinessLogic\Domain\TransactionHistory\Models
 */
class TransactionHistoryModelTest extends BaseTestCase
{
    /**
     * @throws Exception
     */
    public function testGetFirstItem(): void
    {
        // arrange

        $items = [
            new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3')
        ];
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            $items
        );

        // act
        $firstItem = $transactionHistory->collection()->first();

        // assert
        self::assertEquals(new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()),
            'status1'), $firstItem);
    }

    /**
     * @throws Exception
     */
    public function testGetLastItem(): void
    {
        // arrange
        $items = [
            new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3')
        ];
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            $items
        );

        // act
        $firstItem = $transactionHistory->collection()->last();

        // assert
        self::assertEquals(new HistoryItem('id3', 'type3', 'date3', Amount::fromFloat(1, Currency::getDefault()),
            'status3'), $firstItem);
    }

    /**
     * @throws Exception
     */
    public function testSaveGetAllHistoryItems(): void
    {
        // arrange

        $items = [
            new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3')
        ];
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            $items
        );

        // act
        $allItems = $transactionHistory->collection()->getAll();

        // assert

        self::assertCount(3, $allItems);
        self::assertEquals($items, $allItems);
    }

    /**
     * @throws Exception
     */
    public function testSaveAddingHistoryItem(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            [
                new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
                new HistoryItem('id2', 'type2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
                new HistoryItem('id3', 'type3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3')
            ]
        );

        $item = new HistoryItem('id4', 'type4', 'date4', Amount::fromFloat(1, Currency::getDefault()), 'status4');

        // act
        $transactionHistory->collection()->add($item);

        // assert

        self::assertCount(4, $transactionHistory->collection()->getAll());
        self::assertEquals($item, $transactionHistory->collection()->last());
    }

    /**
     * @throws Exception
     */
    public function testFilteringByType(): void
    {
        // arrange

        $items = [
            new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3'),
            new HistoryItem('id1', 'type5', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type5', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type5', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3')
        ];

        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            $items
        );

        // act
        $filteredItems = $transactionHistory->collection()->filterByType('type5');

        // assert
        self::assertCount(3, $filteredItems->getAll());
        self::assertEquals([
            new HistoryItem('id1', 'type5', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type5', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type5', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3')
        ], $filteredItems->getAll()
        );
    }

    /**
     * @throws Exception
     */
    public function testGetChargeItems(): void
    {
        // arrange
        $items = [
            new HistoryItem('id1', 'type1', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3'),
            new HistoryItem('id1', 'type5', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type5', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type5', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3')
        ];

        $chargeItems = [
            new ChargeHistoryItem('id11', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                Amount::fromFloat(5, Currency::getDefault())),
            new ChargeHistoryItem('id22', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                Amount::fromFloat(5, Currency::getDefault())),
            new ChargeHistoryItem('id33', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                Amount::fromFloat(5, Currency::getDefault())),
            new ChargeHistoryItem('id44', 'date1', Amount::fromFloat(10, Currency::getDefault()), 'status1',
                Amount::fromFloat(5, Currency::getDefault())),
        ];

        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            array_merge($items, $chargeItems)
        );

        // act
        $fetchedChargeItems = $transactionHistory->collection()->chargeItems()->getAll();

        // assert
        self::assertCount(4, $fetchedChargeItems);
        self::assertEquals($fetchedChargeItems, $chargeItems);
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws InvalidCurrencyCode
     * @throws Exception
     */
    public function testFromUnzerPayment(): void
    {
        // arrange

        $payment = new Payment();
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

        // act
        $transactionHistory = TransactionHistory::fromUnzerPayment($payment);

        // assert

        $authorizationItems = $transactionHistory->collection()->filterByType(TransactionTypes::AUTHORIZATION);
        $chargeItems = $transactionHistory->collection()->filterByType(TransactionTypes::CHARGE);
        $reversalItems = $transactionHistory->collection()->filterByType(TransactionTypes::REVERSAL);
        $refundItems = $transactionHistory->collection()->filterByType(TransactionTypes::REFUND);
        $shipmentItems = $transactionHistory->collection()->filterByType(TransactionTypes::SHIPMENT);
        $payoutItems = $transactionHistory->collection()->filterByType(TransactionTypes::PAYOUT);
        $chargebackItems = $transactionHistory->collection()->filterByType(TransactionTypes::CHARGEBACK);

        self::assertEquals(PaymentMethodTypes::CARDS, $transactionHistory->getType());
        self::assertEquals('payment1', $transactionHistory->getPaymentId());
        self::assertEquals('order1', $transactionHistory->getOrderId());
        self::assertEquals(Amount::fromFloat(1000, Currency::fromIsoCode('EUR')),
            $transactionHistory->getTotalAmount());
        self::assertEquals(Amount::fromFloat(900, Currency::fromIsoCode('EUR')),
            $transactionHistory->getChargedAmount());
        self::assertEquals(Amount::fromFloat(500, Currency::fromIsoCode('EUR')),
            $transactionHistory->getCancelledAmount());
        self::assertEquals(Amount::fromFloat(100, Currency::fromIsoCode('EUR')),
            $transactionHistory->getRemainingAmount());

        self::assertCount(10, $transactionHistory->collection()->getAll());

        self::assertCount(1, $authorizationItems->getAll());
        self::assertEquals(Amount::fromFloat(1000, Currency::fromIsoCode('EUR')),
            $authorizationItems->first()->getAmount());
        self::assertEquals('authId', $authorizationItems->first()->getId());
        self::assertEquals('2024-10-21 15:58:08', $authorizationItems->first()->getDate());
        self::assertEquals(TransactionTypes::AUTHORIZATION, $authorizationItems->first()->getType());
        self::assertEquals('success', $authorizationItems->first()->getStatus());
        self::assertCount(2, $chargeItems->getAll());
        self::assertEquals(Amount::fromFloat(50, Currency::fromIsoCode('EUR')),
            $chargeItems->first()->getAmount());
        self::assertEquals('chargeId1', $chargeItems->first()->getId());
        self::assertEquals('2024-10-21 16:58:08', $chargeItems->first()->getDate());
        self::assertEquals(TransactionTypes::CHARGE, $chargeItems->first()->getType());
        self::assertEquals('success', $chargeItems->first()->getStatus());
        self::assertEquals(Amount::fromFloat(60, Currency::fromIsoCode('EUR')),
            $chargeItems->last()->getAmount());
        self::assertEquals('chargeId2', $chargeItems->last()->getId());
        self::assertEquals('2024-10-21 17:58:08', $chargeItems->last()->getDate());
        self::assertEquals(TransactionTypes::CHARGE, $chargeItems->last()->getType());
        self::assertEquals('success', $chargeItems->last()->getStatus());
        self::assertCount(1, $reversalItems->getAll());
        self::assertEquals(Amount::fromFloat(20, Currency::fromIsoCode('EUR')),
            $reversalItems->last()->getAmount());
        self::assertEquals('reversalId', $reversalItems->last()->getId());
        self::assertEquals('2024-10-22 17:58:08', $reversalItems->last()->getDate());
        self::assertEquals(TransactionTypes::REVERSAL, $reversalItems->last()->getType());
        self::assertEquals('success', $reversalItems->last()->getStatus());
        self::assertCount(3, $refundItems->getAll());
        self::assertEquals(Amount::fromFloat(44, Currency::fromIsoCode('EUR')),
            $refundItems->last()->getAmount());
        self::assertEquals('refundId', $refundItems->last()->getId());
        self::assertEquals('2024-10-23 17:58:08', $refundItems->last()->getDate());
        self::assertEquals(TransactionTypes::REFUND, $refundItems->last()->getType());
        self::assertEquals('success', $refundItems->last()->getStatus());
        self::assertCount(1, $shipmentItems->getAll());
        self::assertEquals(Amount::fromFloat(11, Currency::fromIsoCode('EUR')),
            $shipmentItems->last()->getAmount());
        self::assertEquals('shipmentId', $shipmentItems->last()->getId());
        self::assertEquals('2024-10-24 17:58:08', $shipmentItems->last()->getDate());
        self::assertEquals(TransactionTypes::SHIPMENT, $shipmentItems->last()->getType());
        self::assertEquals('success', $shipmentItems->last()->getStatus());
        self::assertCount(1, $payoutItems->getAll());
        self::assertEquals(Amount::fromFloat(21, Currency::fromIsoCode('EUR')),
            $payoutItems->last()->getAmount());
        self::assertEquals('payoutId', $payoutItems->last()->getId());
        self::assertEquals('2024-10-25 17:58:08', $payoutItems->last()->getDate());
        self::assertEquals(TransactionTypes::PAYOUT, $payoutItems->last()->getType());
        self::assertEquals('success', $payoutItems->last()->getStatus());
        self::assertCount(1, $chargebackItems->getAll());
        self::assertEquals(Amount::fromFloat(60, Currency::fromIsoCode('EUR')),
            $chargebackItems->last()->getAmount());
        self::assertEquals('chargeBackId', $chargebackItems->last()->getId());
        self::assertEquals('2024-10-26 17:58:08', $chargebackItems->last()->getDate());
        self::assertEquals(TransactionTypes::CHARGEBACK, $chargebackItems->last()->getType());
        self::assertEquals('success', $chargebackItems->last()->getStatus());
    }

    /**
     * @return void
     */
    public function testIsEqualTrue(): void
    {
        // arrange
        $transactionHistory1 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
        );

        $transactionHistory2 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
        );

        // act
        $isEqual = $transactionHistory1->isEqual($transactionHistory2);

        // assert
        self::assertTrue($isEqual);
    }

    /**
     * @return void
     */
    public function testIsEqualFalse(): void
    {
        // arrange
        $transactionHistory1 = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
        );

        $transactionHistory2 = new TransactionHistory(
            PaymentMethodTypes::CARDS,
            'payment11',
            'order11',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(111.11, Currency::getDefault()),
            Amount::fromFloat(12.11, Currency::getDefault()),
            Amount::fromFloat(15.11, Currency::getDefault()),
            Amount::fromFloat(15.11, Currency::getDefault()),
        );

        // act
        $isEqual = $transactionHistory1->isEqual($transactionHistory2);

        // assert
        self::assertFalse($isEqual);
    }

    /**
     * @throws Exception
     */
    public function testGetAuthorizedItem(): void
    {
        // arrange

        $authorizedItem = new AuthorizeHistoryItem('id1', 'type1', Amount::fromFloat(1, Currency::getDefault()),
            'status1',
            Amount::fromFloat(1, Currency::getDefault()));

        $items = [
            $authorizedItem,
            new HistoryItem('id2', 'type2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3'),
            new HistoryItem('id1', 'type5', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type5', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type5', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3')
        ];

        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            $items
        );

        // act
        $item = $transactionHistory->collection()->authorizedItem();

        // assert
        self::assertEquals($item, $authorizedItem);
    }

    /**
     * @throws Exception
     */
    public function testGetAuthorizedItemNoItem(): void
    {
        // arrange

        $items = [
            new HistoryItem('id2', 'type2', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type3', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3'),
            new HistoryItem('id1', 'type5', 'date1', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id2', 'type5', 'date2', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type5', 'date3', Amount::fromFloat(1, Currency::getDefault()), 'status3')
        ];

        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            $items
        );

        // act
        $item = $transactionHistory->collection()->authorizedItem();

        // assert
        self::assertNull($item);
    }

    /**
     * @throws Exception
     */
    public function testSorting(): void
    {
        // arrange

        $authorizedItem = new AuthorizeHistoryItem('id1', '2024-10-28 09:11:43', Amount::fromFloat(1, Currency::getDefault()),
            'status1',
            Amount::fromFloat(1, Currency::getDefault()));

        $items = [
            $authorizedItem,
            new HistoryItem('id2', 'type2', '2024-10-28 09:11:44', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id3', 'type3', '2024-10-28 09:11:45', Amount::fromFloat(1, Currency::getDefault()), 'status3'),
            new HistoryItem('id4', 'type5', '2024-10-28 09:11:46', Amount::fromFloat(1, Currency::getDefault()), 'status1'),
            new HistoryItem('id5', 'type5', '2024-10-28 09:11:47', Amount::fromFloat(1, Currency::getDefault()), 'status2'),
            new HistoryItem('id6', 'type5', '2024-10-28 09:11:48', Amount::fromFloat(1, Currency::getDefault()), 'status3')
        ];

        $transactionHistory = new TransactionHistory(
            PaymentMethodTypes::APPLE_PAY,
            'payment1',
            'order1',
            new PaymentState(1, 'paid'),
            Amount::fromFloat(11.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            Amount::fromFloat(1.11, Currency::getDefault()),
            null,
            $items
        );

        // act
        $items = $transactionHistory->collection()->sortByDateDecreasing()->getAll();

        // assert
        self::assertCount(6, $items);
        self::assertEquals('id6', $items[0]->getId());
        self::assertEquals('id5', $items[1]->getId());
        self::assertEquals('id4', $items[2]->getId());
        self::assertEquals('id3', $items[3]->getId());
        self::assertEquals('id2', $items[4]->getId());
        self::assertEquals('id1', $items[5]->getId());
    }
}
