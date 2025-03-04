<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\SdkPaymentTypes;
use UnzerSDK\Constants\TransactionStatus;
use UnzerSDK\Constants\TransactionTypes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Constants\PaymentState as SdkPaymentState;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;

/**
 * Class TransactionHistory.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models
 */
class TransactionHistory
{
    /**
     * Payment type used for transaction.
     *
     * @var string $type
     */
    private string $type;

    /**
     * Shop order ID.
     *
     * @var string $orderId
     */
    private string $orderId;

    /**
     * @var string $currency
     */
    private string $currency;

    /**
     * @var ?PaymentState $paymentState
     */
    private ?PaymentState $paymentState;

    /**
     * @var ?Amount $totalAmount
     */
    private ?Amount $totalAmount;

    /**
     * @var ?Amount $chargedAmount
     */
    private ?Amount $chargedAmount;

    /**
     * Cancelled/refunded amount
     *
     * @var ?Amount $cancelledAmount
     */
    private ?Amount $cancelledAmount;

    /**
     * @var ?Amount $remainingAmount
     */
    private ?Amount $remainingAmount;

    /**
     * @var HistoryItemCollection
     */
    private HistoryItemCollection $historyItemCollection;

    /**
     * @param string $type
     * @param string $orderId
     * @param string $currency
     * @param PaymentState|null $paymentState
     * @param Amount|null $totalAmount
     * @param Amount|null $chargedAmount
     * @param Amount|null $cancelledAmount
     * @param Amount|null $remainingAmount
     * @param HistoryItem[] $historyItems
     */
    public function __construct(
        string $type,
        string $orderId,
        string $currency,
        ?PaymentState $paymentState = null,
        ?Amount $totalAmount = null,
        ?Amount $chargedAmount = null,
        ?Amount $cancelledAmount = null,
        ?Amount $remainingAmount = null,
        array $historyItems = []
    ) {
        $this->type = $type;
        $this->orderId = $orderId;
        $this->currency = $currency;
        $this->paymentState = $paymentState;
        $this->totalAmount = $totalAmount;
        $this->chargedAmount = $chargedAmount;
        $this->cancelledAmount = $cancelledAmount;
        $this->remainingAmount = $remainingAmount;
        $this->historyItemCollection = new HistoryItemCollection();

        foreach ($historyItems as $item) {
            $this->historyItemCollection->add($item);
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     *
     * @return void
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return PaymentState|null
     */
    public function getPaymentState(): ?PaymentState
    {
        return $this->paymentState;
    }

    /**
     * @param PaymentState|null $paymentState
     *
     * @return void
     */
    public function setPaymentState(?PaymentState $paymentState): void
    {
        $this->paymentState = $paymentState;
    }

    /**
     * @return Amount|null
     */
    public function getTotalAmount(): ?Amount
    {
        return $this->totalAmount;
    }

    /**
     * @param Amount|null $totalAmount
     *
     * @return void
     */
    public function setTotalAmount(?Amount $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    /**
     * @return Amount|null
     */
    public function getChargedAmount(): ?Amount
    {
        return $this->chargedAmount;
    }

    /**
     * @param Amount|null $chargedAmount
     *
     * @return void
     */
    public function setChargedAmount(?Amount $chargedAmount): void
    {
        $this->chargedAmount = $chargedAmount;
    }

    /**
     * @return Amount|null
     */
    public function getCancelledAmount(): ?Amount
    {
        return $this->cancelledAmount;
    }

    /**
     * @param Amount|null $cancelledAmount
     *
     * @return void
     */
    public function setCancelledAmount(?Amount $cancelledAmount): void
    {
        $this->cancelledAmount = $cancelledAmount;
    }

    /**
     * @return Amount|null
     */
    public function getRemainingAmount(): ?Amount
    {
        return $this->remainingAmount;
    }

    /**
     * @param Amount|null $remainingAmount
     *
     * @return void
     */
    public function setRemainingAmount(?Amount $remainingAmount): void
    {
        $this->remainingAmount = $remainingAmount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return array
     */
    public function historyItemCollectionToArray(): array
    {
        $historyItemCollection = [];

        foreach ($this->historyItemCollection->getAll() as $transactionHistoryItem) {
            $historyItemCollection[] = $transactionHistoryItem->toArray();
        }

        return $historyItemCollection;
    }

    /**
     * @return Amount
     *
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     */
    public function getRefundedAmount(): Amount
    {
        $chargedItems = $this->collection()->chargeItems();

        if ($chargedItems->isEmpty()) {
            return Amount::fromInt(0, Currency::fromIsoCode($this->currency));
        }

        return array_reduce($this->collection()->chargeItems()->getAll(), static function (
            ?Amount $totalAmount,
            ChargeHistoryItem $item
        ) {
            return $totalAmount ? $totalAmount->plus($item->getCancelledAmount()) : $item->getCancelledAmount();
        });
    }

    /**
     * @return HistoryItemCollection
     */
    public function collection(): HistoryItemCollection
    {
        return $this->historyItemCollection;
    }

    /**
     * @param Payment $payment
     *
     * @return self
     *
     * @throws InvalidCurrencyCode
     * @throws UnzerApiException
     */
    public static function fromUnzerPayment(Payment $payment): self
    {
        $paymentType = $payment->getPaymentType() ?
            (SdkPaymentTypes::PAYMENT_TYPES[get_class($payment->getPaymentType())] ?? '') : '';

        $transactionHistory = new self(
            $paymentType,
            $payment->getOrderId() ?? '',
            $payment->getCurrency() ?? '',
        );

        $currency = Currency::fromIsoCode($payment->getCurrency());
        $transactionHistory->setTotalAmount(Amount::fromFloat($payment->getAmount()->getTotal(), $currency));
        $transactionHistory->setChargedAmount(Amount::fromFloat($payment->getAmount()->getCharged(), $currency));
        $transactionHistory->setCancelledAmount(Amount::fromFloat($payment->getAmount()->getCanceled(), $currency));
        $transactionHistory->setRemainingAmount(Amount::fromFloat($payment->getAmount()->getRemaining(), $currency));
        $transactionHistory->setPaymentState(
            new PaymentState($payment->getState(), SdkPaymentState::mapStateCodeToName($payment->getState()))
        );

        $historyItems = [];

        if ($authorization = $payment->getAuthorization(true)) {
            $historyItems[] = new AuthorizeHistoryItem(
                $authorization->getId(),
                $authorization->getDate(),
                Amount::fromFloat($authorization->getAmount() ?? 0, $currency),
                self::getStatusLabelFromTransaction($authorization),
                Amount::fromFloat($authorization->getCancelledAmount() ?? 0, $currency),
                $paymentType,
                $payment->getId()
            );
        }

        foreach ($payment->getCharges() as $charge) {
            $historyItems[] = new ChargeHistoryItem(
                $charge->getId() ?? '',
                $charge->getDate() ?? '',
                Amount::fromFloat($charge->getAmount() ?? 0, $currency),
                self::getStatusLabelFromTransaction($charge),
                Amount::fromFloat($charge->getCancelledAmount() ?? 0, $currency),
                $paymentType,
                $payment->getId()
            );
        }

        foreach ($payment->getCancellations() as $refund) {
            $historyItems[] = new HistoryItem(
                $refund->getId() ?? '',
                TransactionTypes::REFUND,
                $refund->getDate() ?? '',
                Amount::fromFloat($refund->getAmount() ?? 0, $currency),
                self::getStatusLabelFromTransaction($refund),
                $paymentType,
                $payment->getId()
            );
        }

        foreach ($payment->getReversals() as $reversal) {
            $historyItems[] = new HistoryItem(
                $reversal->getId() ?? '',
                TransactionTypes::REVERSAL,
                $reversal->getDate() ?? '',
                Amount::fromFloat($reversal->getAmount() ?? 0, $currency),
                self::getStatusLabelFromTransaction($reversal),
                $paymentType,
                $payment->getId()
            );
        }

        foreach ($payment->getRefunds() as $refund) {
            $historyItems[] = new HistoryItem(
                $refund->getId() ?? '',
                TransactionTypes::REFUND,
                $refund->getDate() ?? '',
                Amount::fromFloat($refund->getAmount() ?? 0, $currency),
                self::getStatusLabelFromTransaction($refund),
                $paymentType,
                $payment->getId()
            );
        }

        foreach ($payment->getShipments() as $shipment) {
            $historyItems[] = new HistoryItem(
                $shipment->getId() ?? '',
                TransactionTypes::SHIPMENT,
                $shipment->getDate() ?? '',
                Amount::fromFloat($shipment->getAmount() ?? 0, $currency),
                self::getStatusLabelFromTransaction($shipment),
                $paymentType,
                $payment->getId()
            );
        }

        if ($payout = $payment->getPayout(true)) {
            $historyItems[] = new HistoryItem(
                $payout->getId() ?? '',
                TransactionTypes::PAYOUT,
                $payout->getDate() ?? '',
                Amount::fromFloat($payout->getAmount() ?? 0, $currency),
                self::getStatusLabelFromTransaction($payout),
                $paymentType,
                $payment->getId()
            );
        }

        foreach ($payment->getChargebacks() as $chargeback) {
            $historyItems[] = new HistoryItem(
                $chargeback->getId() ?? '',
                TransactionTypes::CHARGEBACK,
                $chargeback->getDate() ?? '',
                Amount::fromFloat($chargeback->getAmount() ?? 0, $currency),
                self::getStatusLabelFromTransaction($chargeback),
                $paymentType,
                $payment->getId()
            );
        }

        $transactionHistory->setHistoryItemCollection(new HistoryItemCollection($historyItems));

        return $transactionHistory;
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return bool
     */
    public function isEqual(TransactionHistory $transactionHistory): bool
    {
        return $this->type === $transactionHistory->type &&
            $this->orderId === $transactionHistory->orderId &&
            ($this->totalAmount && $transactionHistory->totalAmount &&
                $this->totalAmount->getValue() === $transactionHistory->totalAmount->getValue()) &&
            ($this->chargedAmount && $transactionHistory->chargedAmount && $this->chargedAmount->getValue() ===
                $transactionHistory->chargedAmount->getValue()) &&
            ($this->cancelledAmount && $transactionHistory->cancelledAmount && $this->cancelledAmount->getValue() ===
                $transactionHistory->cancelledAmount->getValue()) &&
            ($this->remainingAmount && $transactionHistory->remainingAmount &&
                $this->remainingAmount->getValue() === $transactionHistory->remainingAmount->getValue()) &&
            $this->collection()->isEqual($transactionHistory->collection()) &&
            $this->getPaymentState()->getId() === $transactionHistory->getPaymentState()->getId();
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return void
     */
    public function synchronizeHistoryItems(TransactionHistory $transactionHistory): void
    {
        $existingItems = [];
        foreach ($this->collection()->getAll() as $item) {
            $key = $item->getId() . '_' . $item->getPaymentType() . '_' . $item->getPaymentId();
            $existingItems[] = $key;
        }

        foreach ($transactionHistory->collection()->getAll() as $item) {
            $key = $item->getId() . '_' . $item->getPaymentType() . '_' . $item->getPaymentId();
            if (!in_array($key, $existingItems)) {
                $this->collection()->add($item);
            }
        }
    }

    /**
     * @param HistoryItemCollection $historyItemCollection
     *
     * @return void
     */
    private function setHistoryItemCollection(HistoryItemCollection $historyItemCollection): void
    {
        $this->historyItemCollection = $historyItemCollection;
    }

    /**
     * @param AbstractTransactionType $transactionType
     *
     * @return string
     */
    private static function getStatusLabelFromTransaction(AbstractTransactionType $transactionType): string
    {
        if ($transactionType->isSuccess()) {
            return ucfirst(TransactionStatus::STATUS_SUCCESS);
        }

        if ($transactionType->isError()) {
            return ucfirst(TransactionStatus::STATUS_ERROR);
        }
        if ($transactionType->isPending()) {
            return ucfirst(TransactionStatus::STATUS_PENDING);
        }

        return ucfirst(TransactionStatus::STATUS_RESUMED);
    }
}
