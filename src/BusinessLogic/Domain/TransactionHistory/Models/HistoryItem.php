<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use UnzerSDK\Constants\TransactionTypes;

/**
 * Class HistoryItem.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models
 */
class HistoryItem
{
    /** @var string $id */
    private string $id;

    /** @var string $type */
    private string $type;

    /** @var string $date */
    private string $date;

    /** @var Amount $amount */
    private Amount $amount;

    /** @var string $status */
    private string $status;

    /**
     * @param string $id
     * @param string $type
     * @param string $date
     * @param Amount $amount
     * @param string $status
     */
    public function __construct(string $id, string $type, string $date, Amount $amount, string $status)
    {
        $this->id = $id;
        $this->type = $type;
        $this->date = $date;
        $this->amount = $amount;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
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
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     *
     * @return void
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     *
     * @return void
     */
    public function setAmount(Amount $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @param array $historyItemData
     *
     * @return self[]
     * @throws InvalidCurrencyCode
     */
    public static function fromBatchArray(array $historyItemData): array
    {
        $historyItems = [];

        foreach ($historyItemData as $itemData) {
            if($itemData['type'] === TransactionTypes::CHARGE) {
                $historyItems[] = ChargeHistoryItem::fromArray($itemData);

                continue;
            }

            if($itemData['type'] === TransactionTypes::AUTHORIZATION) {
                $historyItems[] = AuthorizeHistoryItem::fromArray($itemData);

                continue;
            }

            $historyItems[] = self::fromArray($itemData);
        }

        return $historyItems;
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
            $historyItem['type'] ?? '',
            $historyItem['date'] ?? '',
            $historyItem['amount'] ? Amount::fromArray($historyItem['amount']) : [],
            $historyItem['status'] ?? ''
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
            'status' => $this->getStatus()
        ];
    }
}
