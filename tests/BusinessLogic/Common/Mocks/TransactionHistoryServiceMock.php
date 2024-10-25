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

    /** @var array  */
    private array $callHistory = [];

    private int $timeSaved = 0;

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
        $this->callHistory['saveTransactionHistory'] = ['transactionId' => $transactionHistory->getPaymentId(), 'count' => ++$this->timeSaved];

        $this->transactionHistory = $transactionHistory;
    }

    /**
     * @param string $methodName
     *
     * @return array
     */
    public function getCallHistory(string $methodName): array
    {
        return !empty($this->callHistory[$methodName]) ? $this->callHistory[$methodName] : [];
    }
}
