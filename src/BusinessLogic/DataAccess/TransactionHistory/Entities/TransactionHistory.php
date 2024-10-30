<?php

namespace Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;
use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory as DomainTransactionHistory;

/**
 * Class TransactionHistory.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities
 */
class TransactionHistory extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected string $storeId;

    /**
     * @var string
     */
    protected string $orderId;

    /**
     * @var int
     */
    protected int $updatedAt;

    /**
     * @var DomainTransactionHistory
     */
    protected DomainTransactionHistory $transactionHistory;

    /**
     * @inheritDoc
     *
     * @throws InvalidCurrencyCode
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];
        $this->orderId = $data['orderId'];
        $this->updatedAt = $data['updatedAt'];

        $transactionHistory = $data['transactionHistory'] ?? [];
        $this->transactionHistory = new DomainTransactionHistory(
            $transactionHistory['type'],
            $transactionHistory['paymentId'],
            $transactionHistory['orderId'],
            !empty($transactionHistory['paymentState']) ? PaymentState::fromArray($transactionHistory['paymentState']) : null,
            !empty($transactionHistory['totalAmount']) ? Amount::fromArray($transactionHistory['totalAmount']) : null,
            !empty($transactionHistory['chargedAmount']) ? Amount::fromArray($transactionHistory['chargedAmount']) : null,
            !empty($transactionHistory['cancelledAmount']) ? Amount::fromArray($transactionHistory['cancelledAmount']) : null,
            !empty($transactionHistory['remainingAmount']) ? Amount::fromArray($transactionHistory['remainingAmount']) : null,
            HistoryItem::fromBatchArray($transactionHistory['historyItems'] ?? []),
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['orderId'] = $this->orderId;
        $data['updatedAt'] = $this->updatedAt;
        $data['transactionHistory'] = [
            'type' => $this->transactionHistory->getType(),
            'paymentId' => $this->transactionHistory->getPaymentId(),
            'orderId' => $this->transactionHistory->getOrderId(),
            'paymentState' => $this->transactionHistory->getPaymentState() ? $this->transactionHistory->getPaymentState()->toArray() : null,
            'totalAmount' => $this->transactionHistory->getTotalAmount() ? $this->transactionHistory->getTotalAmount()->toArray() : null,
            'chargedAmount' => $this->transactionHistory->getChargedAmount() ? $this->transactionHistory->getChargedAmount()->toArray() : null,
            'cancelledAmount' => $this->transactionHistory->getCancelledAmount() ? $this->transactionHistory->getCancelledAmount()->toArray() : null,
            'remainingAmount' => $this->transactionHistory->getRemainingAmount() ? $this->transactionHistory->getRemainingAmount()->toArray() : null,
            'historyItems' => $this->transactionHistory->historyItemCollectionToArray()
        ];

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId')
            ->addStringIndex('orderId')
            ->addIntegerIndex('updatedAt');

        return new EntityConfiguration($indexMap, 'TransactionHistory');
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return DomainTransactionHistory
     */
    public function getTransactionHistory(): DomainTransactionHistory
    {
        return $this->transactionHistory;
    }

    /**
     * @param DomainTransactionHistory $transactionHistory
     *
     * @return void
     */
    public function setTransactionHistory(DomainTransactionHistory $transactionHistory): void
    {
        $this->transactionHistory = $transactionHistory;
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
     * @return int
     */
    public function getUpdatedAt(): int
    {
        return $this->updatedAt;
    }

    /**
     * @param int $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt(int $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
