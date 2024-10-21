<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services;

use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;

/**
 * Class TransactionHistoryService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services
 */
class TransactionHistoryService
{
    /** @var TransactionHistoryRepositoryInterface $transactionHistoryRepository */
    private TransactionHistoryRepositoryInterface $transactionHistoryRepository;

    /**
     * @param TransactionHistoryRepositoryInterface $transactionHistoryRepository
     */
    public function __construct(TransactionHistoryRepositoryInterface $transactionHistoryRepository)
    {
        $this->transactionHistoryRepository = $transactionHistoryRepository;
    }

    /**
     * @param string $orderId
     *
     * @return TransactionHistory|null
     */
    public function getTransactionHistoryByOrderId(string $orderId): ?TransactionHistory
    {
        return $this->transactionHistoryRepository->getTransactionHistoryByOrderId($orderId);
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return void
     */
    public function saveTransactionHistory(TransactionHistory $transactionHistory): void
    {
        $this->transactionHistoryRepository->setTransactionHistory($transactionHistory);
    }
}
