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

    /** @var array $orderIds */
    private array $orderIds = [];

    /**
     * @var array
     */
    private array $paymentIds = [];

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
        $this->callHistory['saveTransactionHistory'] = ['transactionId' => $transactionHistory->getOrderId(), 'count' => ++$this->timeSaved];

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

    /**
     * @return array|string[]
     */
    public function getPaymentIdsForSynchronization(): array
    {
        return $this->paymentIds;
    }

    /**
     * @param array $orderIds
     *
     * @return void
     */
    public function setPaymentIdsForSynchronization(array $paymentIds): void
    {
        $this->paymentIds = $paymentIds;
    }
}
