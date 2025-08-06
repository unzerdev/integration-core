<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\ChargeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class OrderManagementService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\OrderManagement\Services
 */
class OrderManagementService
{
    /** @var UnzerFactory $unzerFactory */
    private UnzerFactory $unzerFactory;

    /** @var TransactionHistoryService $transactionHistoryService */
    private TransactionHistoryService $transactionHistoryService;

    /**
     * @param UnzerFactory $unzerFactory
     * @param TransactionHistoryService $transactionHistoryService
     */
    public function __construct(UnzerFactory $unzerFactory, TransactionHistoryService $transactionHistoryService)
    {
        $this->unzerFactory = $unzerFactory;
        $this->transactionHistoryService = $transactionHistoryService;
    }

    /**
     * @param string $orderId
     * @param Amount $chargeAmount
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function chargeOrder(string $orderId, Amount $chargeAmount): void
    {
        if(!($transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId))) {
            return;
        }

        if (!$this->isChargeNecessary($transactionHistory, $chargeAmount)) {
            return;
        }

        $authorizedItem = $transactionHistory->collection()->authorizedItem();

        if(!$authorizedItem) {
            return;
        }

        $this->unzerFactory->makeUnzerAPI()->chargeAuthorization(
            $authorizedItem->getPaymentId(),
            $chargeAmount->getPriceInCurrencyUnits(),
            $transactionHistory->getOrderId()
        );
    }

    /**
     * @param string $orderId
     * @param Amount|null $amount
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function cancelOrder(string $orderId, ?Amount $amount = null): void
    {
        if(!($transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId))) {
            return;
        }
        if (!$this->isCancellationNecessary($transactionHistory, $amount)) {
            return;
        }
        if($amount === null) {
            $amount = $transactionHistory->getTotalAmount();
        }

        $authorizedItem = $transactionHistory->collection()->authorizedItem();

        if(!$authorizedItem) {
            return;
        }

        $this->unzerFactory->makeUnzerAPI()->cancelAuthorizationByPayment(
            $authorizedItem->getPaymentId(),
            $amount->getPriceInCurrencyUnits()
        );
    }

    /**
     * @param string $orderId
     * @param Amount $refundAmount
     *
     * @return void
     * @throws ConnectionSettingsNotFoundException
     * @throws CurrencyMismatchException
     * @throws UnzerApiException
     */
    public function refundOrder(string $orderId, Amount $refundAmount): void
    {
        if(!($transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId))) {
            return;
        }

        if (!$this->isRefundNecessary($transactionHistory, $refundAmount)) {
            return;
        }

        /** @var ChargeHistoryItem[] $chargeItems */
        $chargeItems = $transactionHistory->collection()->chargeItems()->getAll();

        foreach ($chargeItems as $chargeItem) {
            if ($refundAmount->getValue() > $chargeItem->getRefundableAmount()->getValue()) {
                $this->unzerFactory->makeUnzerAPI()->cancelChargeById(
                    $chargeItem->getPaymentId(),
                    $chargeItem->getId(),
                    $chargeItem->getRefundableAmount()->getPriceInCurrencyUnits()
                );
                $refundAmount = $refundAmount->minus($chargeItem->getRefundableAmount());

                continue;
            }

            $this->unzerFactory->makeUnzerAPI()->cancelChargeById(
                $chargeItem->getPaymentId(),
                $chargeItem->getId(),
                $refundAmount->getPriceInCurrencyUnits()
            );

            break;
        }
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return bool
     */
    private function isTransactionHistoryValid(TransactionHistory $transactionHistory): bool
    {
        return $transactionHistory->getChargedAmount() &&
            $transactionHistory->getCancelledAmount() &&
            $transactionHistory->getTotalAmount() &&
            $transactionHistory->getRemainingAmount() &&
            $transactionHistory->getPaymentState();
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param Amount $amountToCharge
     *
     * @return bool
     */
    private function isChargeNecessary(TransactionHistory $transactionHistory, Amount $amountToCharge): bool
    {
        return $this->isTransactionHistoryValid($transactionHistory) &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_CANCELED &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_CREATE &&
            $transactionHistory->getRemainingAmount() &&
            $transactionHistory->getRemainingAmount()->getValue() &&
            $amountToCharge->getValue() <= $transactionHistory->getRemainingAmount()->getValue();
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param Amount|null $amount
     *
     * @return bool
     */
    private function isCancellationNecessary(TransactionHistory $transactionHistory, ?Amount $amount = null): bool
    {
        if($amount === null) {
            return true;
        }

        return $this->isTransactionHistoryValid($transactionHistory) &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_CANCELED &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_CREATE &&
            $transactionHistory->getRemainingAmount()->getValue() &&
            $amount->getValue() <= $transactionHistory->getRemainingAmount()->getValue();
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param Amount $amountToRefund
     *
     * @return bool
     *
     * @throws CurrencyMismatchException
     */
    private function isRefundNecessary(TransactionHistory $transactionHistory, Amount $amountToRefund): bool
    {
        return $this->isTransactionHistoryValid($transactionHistory) &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_PENDING &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_CANCELED &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_CREATE &&
            $transactionHistory->getCancelledAmount()->plus($transactionHistory->getChargedAmount())->getValue() ===
            $transactionHistory->getTotalAmount()->getValue() &&
            $amountToRefund->getValue() <= $transactionHistory->getChargedAmount()->getValue();
    }
}
