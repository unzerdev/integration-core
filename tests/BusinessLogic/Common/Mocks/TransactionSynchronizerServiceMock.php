<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Service\TransactionSynchronizerService;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;

/**
 * Class TransactionSynchronizerServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class TransactionSynchronizerServiceMock extends TransactionSynchronizerService
{
    /** @var array */
    private array $callHistory = [];

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
     * @param string $orderId
     *
     * @return void
     */
    public function synchronizeTransactions(string $orderId): void
    {
        $this->callHistory['synchronizeTransactions'][] = ['orderId' => $orderId];
    }

    /**
     * @param Payment $payment
     *
     * @return TransactionHistory
     * @throws UnzerApiException
     * @throws InvalidCurrencyCode
     */
    public function getAndUpdateTransactionHistoryFromUnzerPayment(Payment $payment): TransactionHistory
    {
        $this->callHistory['getAndUpdateTransactionHistoryFromUnzerPayment'][] = ['orderId' => $payment->getOrderId()];

        return TransactionHistory::fromUnzerPayment($payment);
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return void
     */
    public function handleOrderStatusChange(TransactionHistory $transactionHistory): void
    {
        $this->callHistory['handleOrderStatusChange'][] = ['orderId' => $transactionHistory->getOrderId()];
    }

    /**
     * @param TransactionHistory $history
     *
     * @return void     *
     */
    public function handleRefund(TransactionHistory $history): void
    {
        $this->callHistory['handleRefund'][] = ['orderId' => $history->getOrderId()];
    }

    /**
     * @param TransactionHistory $history
     *
     * @return void
     *
     */
    public function handleCancellation(TransactionHistory $history): void
    {
        $this->callHistory['handleCancellation'][] = ['orderId' => $history->getOrderId()];
    }

    /**
     * @param TransactionHistory $history
     *
     * @return void
     */
    public function handleCharge(TransactionHistory $history): void
    {
        $this->callHistory['handleCharge'][] = ['orderId' => $history->getOrderId()];
    }
}
