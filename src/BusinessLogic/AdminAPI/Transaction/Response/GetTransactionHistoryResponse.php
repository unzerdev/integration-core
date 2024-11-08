<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Transaction\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;

/**
 * Class GetTransactionHistoryResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Transaction\Response
 */
class GetTransactionHistoryResponse extends Response
{
    /**
     * @var TransactionHistory|null $transactionHistory
     */
    private ?TransactionHistory $transactionHistory;

    /**
     * @param TransactionHistory|null $transactionHistory
     */
    public function __construct(?TransactionHistory $transactionHistory)
    {
        $this->transactionHistory = $transactionHistory;
    }

    /**
     * @inheritDoc
     *
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     */
    public function toArray(): array
    {
        if (!$this->transactionHistory) {
            return [];
        }

        $returnArray['type'] = $this->transactionHistory->getType();
        $returnArray['paymentId'] = $this->transactionHistory->getPaymentId();
        $returnArray['orderId'] = $this->transactionHistory->getOrderId();
        $returnArray['amounts'] =
            [
                'authorized' => $this->amountToArray($this->transactionHistory->getTotalAmount()),
                'charged' => $this->amountToArray($this->transactionHistory->getChargedAmount()),
                'refunded' => $this->amountToArray($this->transactionHistory->getRefundedAmount()),
                'cancelled' => $this->amountToArray($this->transactionHistory->getCancelledAmount()),
            ];

        $historyItems = $this->transactionHistory->collection()->sortByDateDecreasing()->getAll();

        foreach ($historyItems as $historyItem) {
            $returnArray['items'][] = [
                'id' => $historyItem->getId(),
                'date' => $historyItem->getDate(),
                'type' => $historyItem->getType(),
                'amount' => $this->amountToArray($historyItem->getAmount()),
                'status' => $historyItem->getStatus(),
                'paymentType' => $historyItem->getPaymentType(),
                'paymentId' => $historyItem->getPaymentId()
            ];
        }

        return $returnArray;
    }

    /**
     * @param Amount|null $amount
     *
     * @return array
     */
    public function amountToArray(?Amount $amount): array
    {
        if(!$amount) {
            return [];
        }

        return [
            'amount' => $amount->getPriceInCurrencyUnits(),
            'currency' => $amount->getCurrency(),
        ];
    }
}
