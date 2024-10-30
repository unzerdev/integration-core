<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Service;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\Order\OrderServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\AuthorizeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;

/**
 * Class TransactionSynchronizerService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionSynchronization\Service
 */
class TransactionSynchronizerService
{
    /**
     * @var UnzerFactory $unzerFactory
     */
    private UnzerFactory $unzerFactory;

    /**
     * @var TransactionHistoryService $transactionHistoryService
     */
    private TransactionHistoryService $transactionHistoryService;

    /**
     * @var OrderServiceInterface $orderService
     */
    private OrderServiceInterface $orderService;

    /**
     * @var PaymentStatusMapService $paymentStatusMapService
     */
    private PaymentStatusMapService $paymentStatusMapService;

    /**
     * @param UnzerFactory $unzerFactory
     * @param TransactionHistoryService $transactionHistoryService
     * @param OrderServiceInterface $orderService
     * @param PaymentStatusMapService $paymentStatusMapService
     */
    public function __construct(
        UnzerFactory $unzerFactory,
        TransactionHistoryService $transactionHistoryService,
        OrderServiceInterface $orderService,
        PaymentStatusMapService $paymentStatusMapService
    ) {
        $this->unzerFactory = $unzerFactory;
        $this->transactionHistoryService = $transactionHistoryService;
        $this->orderService = $orderService;
        $this->paymentStatusMapService = $paymentStatusMapService;
    }

    /**
     * @param string $orderId
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function synchronizeTransactions(string $orderId): void
    {
        $payment = $this->unzerFactory->makeUnzerAPI()->fetchPayment($orderId);
        $transactionHistory = $this->getAndUpdateTransactionHistoryFromUnzerPayment($payment);
        $this->handleOrderStatusChange($transactionHistory);
        $this->handleCharge($transactionHistory);
        $this->handleCancellation($transactionHistory);
        $this->handleRefund($transactionHistory);
    }

    /**
     * @param Payment $payment
     *
     * @return TransactionHistory
     * @throws InvalidCurrencyCode
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function getAndUpdateTransactionHistoryFromUnzerPayment(Payment $payment): TransactionHistory
    {
        $transactionHistory = TransactionHistory::fromUnzerPayment($payment);
        $existingTransactionHistory =
            $this->transactionHistoryService->getTransactionHistoryByOrderId($transactionHistory->getOrderId());

        if (!$existingTransactionHistory) {
            throw new TransactionHistoryNotFoundException(
                new TranslatableLabel("Transaction history for orderId: {$transactionHistory->getOrderId()} not found.",
                    'transactionHistory.notFound')
            );
        }

        if (!$transactionHistory->isEqual($existingTransactionHistory)) {
            $this->transactionHistoryService->saveTransactionHistory($transactionHistory);
        }

        return $transactionHistory;
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return void
     */
    public function handleOrderStatusChange(TransactionHistory $transactionHistory): void
    {
        $statusMap = $this->paymentStatusMapService->getPaymentStatusMap();

        $newStatus = '';

        if ($transactionHistory->getPaymentState()->getId() === PaymentState::STATE_CANCELED) {
            $newStatus = $statusMap[PaymentStatus::CANCELLED] ?? '';
        }

        if ($transactionHistory->getPaymentState()->getId() === PaymentState::STATE_COMPLETED) {
            $newStatus = $statusMap[PaymentStatus::PAID] ?? '';
        }

        if ($transactionHistory->getPaymentState()->getId() === PaymentState::STATE_PENDING) {
            $newStatus = $statusMap[PaymentStatus::UNPAID] ?? '';
        }

        if ($transactionHistory->getPaymentState()->getId() === PaymentState::STATE_CHARGEBACK) {
            $newStatus = $statusMap[PaymentStatus::CHARGEBACK] ?? '';
        }

        if ($transactionHistory->getCancelledAmount()->getValue() === $transactionHistory->getChargedAmount()->getValue()
            && !$transactionHistory->getRemainingAmount()->getValue()) {
            $newStatus = $statusMap[PaymentStatus::FULL_REFUND] ?? '';
        }

        if ($transactionHistory->getCancelledAmount()->getValue() &&
            $transactionHistory->getCancelledAmount()->getValue() !== $transactionHistory->getChargedAmount()->getValue() &&
            !$transactionHistory->getRemainingAmount()->getValue()) {
            $newStatus = $statusMap[PaymentStatus::PARTIAL_REFUND] ?? '';
        }

        if (!empty($newStatus)) {
            $this->orderService->changeOrderStatus($transactionHistory->getOrderId(), $newStatus);
        }
    }

    /**
     * @param TransactionHistory $history
     *
     * @return void
     *
     * @throws CurrencyMismatchException
     */
    public function handleRefund(TransactionHistory $history): void
    {
        $refundedOnShop = $this->orderService->getRefundedAmountForOrder($history->getOrderId());

        if ($refundedOnShop->getValue() < $history->getRefundedAmount()->getValue()) {
            $this->orderService->refundOrder(
                $history->getOrderId(),
                $history->getCancelledAmount()->minus($refundedOnShop)
            );
        }
    }

    /**
     * @param TransactionHistory $history
     *
     * @return void
     *
     * @throws CurrencyMismatchException
     */
    public function handleCancellation(TransactionHistory $history): void
    {
        $cancelledOnShop = $this->orderService->getCancelledAmountForOrder($history->getOrderId());
        /** @var ?AuthorizeHistoryItem $authorizedItem */
        $authorizedItem = $history->collection()->authorizedItem();

        if ($authorizedItem && $cancelledOnShop->getValue() < $authorizedItem->getCancelledAmount()->getValue()) {
            $this->orderService->cancelOrder(
                $history->getOrderId(),
                $history->getCancelledAmount()->minus($cancelledOnShop),
                !$history->getTotalAmount()->getValue()
            );
        }
    }

    /**
     * @param TransactionHistory $history
     *
     * @return void
     *
     * @throws CurrencyMismatchException
     */
    public function handleCharge(TransactionHistory $history): void
    {
        $chargedOnShop = $this->orderService->getChargeAmountForOrder($history->getOrderId());

        $isFullCharge = $history->getChargedAmount() &&
            $history->getTotalAmount() &&
            $history->getChargedAmount()->getValue() === $history->getTotalAmount()->getValue();

        if ($chargedOnShop->getValue() < $history->getChargedAmount()->getValue()) {
            $this->orderService->chargeOrder(
                $history->getOrderId(),
                $history->getChargedAmount()->minus($chargedOnShop),
                $isFullCharge
            );
        }
    }
}
