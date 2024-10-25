<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Services;

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
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Constants\WebhookEvents;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Constants\PaymentState;

/**
 * Class WebhookService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Webhook\Services
 */
class WebhookService
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
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidCurrencyCode
     * @throws TransactionHistoryNotFoundException
     * @throws CurrencyMismatchException
     */
    public function handle(Webhook $webhook): void
    {
        $resource = $this->unzerFactory->makeUnzerAPI()->fetchResourceFromEvent($webhook->toPayload());

        if (!($resource instanceof Payment)) {
            $resource = $this->unzerFactory->makeUnzerAPI()->fetchPayment($webhook->getPaymentId());
        }

        $transactionHistory = TransactionHistory::fromUnzerPayment($resource);
        $existingTransactionHistory =
            $this->transactionHistoryService->getTransactionHistoryByOrderId($transactionHistory->getOrderId());

        if (!$existingTransactionHistory) {
            throw new TransactionHistoryNotFoundException(
                new TranslatableLabel("Transaction history for orderId:{$transactionHistory->getOrderId()} not found.",
                    'transactionHistory.notFound')
            );
        }

        if ($transactionHistory->isEqual($existingTransactionHistory)) {
            return;
        }

        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);
        $this->handleOrderStatusChange($transactionHistory, $webhook);

        if ($webhook->getEvent() === WebhookEvents::CHARGE_CANCELED) {
            $this->handleRefund($transactionHistory);

            return;
        }

        if ($webhook->getEvent() === WebhookEvents::PAYMENT_CANCELED) {
            $this->handleCancellation($transactionHistory);

            return;
        }

        if ($webhook->getEvent() === WebhookEvents::CHARGE_SUCCEEDED) {
            $this->handleCharge($transactionHistory);
        }
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param Webhook $webhook
     *
     * @return void
     */
    private function handleOrderStatusChange(TransactionHistory $transactionHistory, Webhook $webhook): void
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
    private function handleRefund(TransactionHistory $history): void
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
    private function handleCancellation(TransactionHistory $history): void
    {
        $refundedOnShop = $this->orderService->getCancelledAmountForOrder($history->getOrderId());
        /** @var ?AuthorizeHistoryItem $authorizedItem */
        $authorizedItem = $history->collection()->authorizedItem();

        if ($authorizedItem && $refundedOnShop->getValue() < $authorizedItem->getCancelledAmount()->getValue()) {
            $this->orderService->cancelOrder(
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
    private function handleCharge(TransactionHistory $history): void
    {
        $chargedOnShop = $this->orderService->getChargeAmountForOrder($history->getOrderId());

        if ($chargedOnShop->getValue() < $history->getChargedAmount()->getValue()) {
            $this->orderService->chargeOrder(
                $history->getOrderId(),
                $history->getChargedAmount()->minus($chargedOnShop)
            );
        }
    }
}
