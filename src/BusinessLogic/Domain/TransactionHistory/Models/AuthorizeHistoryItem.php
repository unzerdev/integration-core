<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use UnzerSDK\Constants\TransactionTypes;

/**
 * Class AuthorizeHistoryItem.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models
 */
class AuthorizeHistoryItem extends HistoryItem
{
    /** @var Amount */
    private Amount $cancelledAmount;

    /**
     * @param string $id
     * @param string $date
     * @param Amount $amount
     * @param string $status
     * @param Amount $cancelledAmount
     */
    public function __construct(string $id, string $date, Amount $amount, string $status, Amount $cancelledAmount)
    {
        parent::__construct($id, TransactionTypes::AUTHORIZATION, $date, $amount, $status);

        $this->cancelledAmount = $cancelledAmount;
    }

    /**
     * @return Amount
     */
    public function getCancelledAmount(): Amount
    {
        return $this->cancelledAmount;
    }

    /**
     * @return Amount
     *
     * @throws CurrencyMismatchException
     */
    public function getCancellableAmount(): Amount
    {
        return $this->getAmount()->minus($this->cancelledAmount);
    }

    /**
     * @param array $historyItem
     *
     * @return self
     *
     * @throws InvalidCurrencyCode
     */
    public static function fromArray(array $historyItem): self
    {
        return new self(
            $historyItem['id'] ?? '',
            $historyItem['date'] ?? '',
            $historyItem['amount'] ? Amount::fromArray($historyItem['amount']) : [],
            $historyItem['status'] ?? '',
            $historyItem['cancelledAmount'] ? Amount::fromArray($historyItem['cancelledAmount']) : [],
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'date' => $this->getDate(),
            'amount' => $this->getAmount()->toArray(),
            'status' => $this->getStatus(),
            'cancelledAmount' => $this->getCancelledAmount()->toArray(),
        ];
    }
}
