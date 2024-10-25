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
     * @return HistoryItem
     *
     * @throws AuthorizedItemNotFoundException
     */
    public function authorizedItem(): HistoryItem
    {
        $authorizedItems = new self(
            array_values(array_filter($this->historyItems, static function ($item) {
                return $item->getType() === TransactionTypes::AUTHORIZATION && $item instanceof AuthorizeHistoryItem;
            }))
        );

        if(!($item = $authorizedItems->first())) {
            throw new AuthorizedItemNotFoundException(
                new TranslatableLabel('Authorized item not found', 'authorization.notFound'),
            );
        }

        return $item;
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
}
