<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;

/**
 * Class TransactionHistoryServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class TransactionHistoryServiceMock extends TransactionHistoryService
{
    /**
     * @var ?TransactionHistory $transactionHistory
     */
    private ?TransactionHistory $transactionHistory = null;

    /**
     * @param string $orderId
     *
     * @return TransactionHistory|null
     */
    public function getTransactionHistoryByOrderId(string $orderId): ?TransactionHistory
    {
        return $this->transactionHistory;
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return void
     */
    public function saveTransactionHistory(TransactionHistory $transactionHistory): void
    {
        $this->transactionHistory = $transactionHistory;
    }
}
