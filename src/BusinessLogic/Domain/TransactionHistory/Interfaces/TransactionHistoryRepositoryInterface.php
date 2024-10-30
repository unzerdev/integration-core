<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces;

use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Interface TransactionHistoryRepositoryInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces
 */
interface TransactionHistoryRepositoryInterface
{
    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return void
     */
    public function setTransactionHistory(TransactionHistory $transactionHistory): void;

    /**
     * @param string $orderId
     *
     * @return TransactionHistory|null
     */
    public function getTransactionHistoryByOrderId(string $orderId): ?TransactionHistory;

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteTransactionHistoryEntities(): void;

    /**
     * @param int $timeLimit
     *
     * @return TransactionHistory[]
     */
    public function getTransactionHistoriesByUpdateTime(int $timeLimit): array;
}
