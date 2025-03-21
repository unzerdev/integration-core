<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models;

use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\AuthorizedItemNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use UnzerSDK\Constants\TransactionTypes;

/**
 * Class HistoryItemCollection.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models
 */
class HistoryItemCollection
{
    /**
     * @var HistoryItem[]
     */
    private array $historyItems;

    /**
     * @param HistoryItem[] $historyItems
     */
    public function __construct(array $historyItems = [])
    {
        $this->historyItems = $historyItems;
    }

    /**
     * @return HistoryItem[]
     */
    public function getAll(): array
    {
        return $this->historyItems;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function filterByType(string $type): self
    {
        return new self(
            array_values(array_filter($this->historyItems, static function ($item) use ($type) {
                return $item->getType() === $type;
            }))
        );
    }

    /**
     * @return self
     */
    public function chargeItems(): self
    {
        return new self(
            array_values(array_filter($this->historyItems, static function ($item) {
                return $item->getType() === TransactionTypes::CHARGE && $item instanceof ChargeHistoryItem;
            }))
        );
    }

    /**
     * @return AuthorizeHistoryItem
     */
    public function authorizedItem(): ?HistoryItem
    {
        $authorizedItems = new self(
            array_values(array_filter($this->historyItems, static function ($item) {
                return $item->getType() === TransactionTypes::AUTHORIZATION && $item instanceof AuthorizeHistoryItem;
            }))
        );

        return $authorizedItems->first();
    }

    /**
     * @return self
     */
    public function sortByDateDecreasing(): self
    {
        usort($this->historyItems, function ($a, $b) {
            return strtotime($b->getDate()) <=> strtotime($a->getDate());
        });

        return new self($this->historyItems);
    }

    /**
     * Adds history item to collection.
     *
     * @param HistoryItem $item
     *
     * @return void
     */
    public function add(HistoryItem $item): void
    {
        $this->historyItems[] = $item;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->historyItems);
    }

    /**
     * @return HistoryItem|null
     */
    public function last(): ?HistoryItem
    {
        return !$this->isEmpty() ? end($this->historyItems) : null;
    }

    /**
     * @return HistoryItem|null
     */
    public function first(): ?HistoryItem
    {
        return !$this->isEmpty() ? current($this->historyItems) : null;
    }

    /**
     * @param HistoryItemCollection $historyItemCollection
     *
     * @return bool
     */
    public function isEqual(HistoryItemCollection $historyItemCollection): bool {

        if (count($this->getAll()) !== count($historyItemCollection->getAll())) {
            return false;
        }

        foreach ($this->getAll() as $index => $item1) {
            $item2 = $historyItemCollection->getAll()[$index];

            if (
                $item1->getId() !== $item2->getId() ||
                $item1->getType() !== $item2->getType() ||
                $item1->getDate() !== $item2->getDate() ||
                $item1->getStatus() !== $item2->getStatus() ||
                $item1->getPaymentType() !== $item2->getPaymentType() ||
                $item1->getPaymentId() !== $item2->getPaymentId()
            ) {
                return false;
            }

            if ($item1->getAmount()->toArray() !== $item2->getAmount()->toArray()) {
                return false;
            }
        }

        return true;
    }
}
