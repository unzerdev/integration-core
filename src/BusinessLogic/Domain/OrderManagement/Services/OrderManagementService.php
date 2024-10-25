<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\CancellationNotPossibleException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\ChargeNotPossibleException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\RefundNotPossibleException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidTransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\ChargeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
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
     * @throws ChargeNotPossibleException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidTransactionHistory
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function chargeOrder(string $orderId, Amount $chargeAmount): void
    {
        $transactionHistory = $this->getTransactionHistoryByOrderId($orderId);
        $this->validateChargePossibility($transactionHistory, $chargeAmount);
        $this->unzerFactory->makeUnzerAPI()->chargeAuthorization(
            $transactionHistory->getPaymentId(),
            $chargeAmount->getPriceInCurrencyUnits(),
            $transactionHistory->getOrderId()
        );
    }

    /**
     * @param string $orderId
     * @param Amount $amount
     *
     * @return void
     *
     * @throws CancellationNotPossibleException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidTransactionHistory
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function cancelOrder(string $orderId, Amount $amount): void
    {
        $transactionHistory = $this->getTransactionHistoryByOrderId($orderId);
        $this->validateCancellationPossibility($transactionHistory, $amount);
        $this->unzerFactory->makeUnzerAPI()->cancelAuthorizationByPayment(
            $transactionHistory->getPaymentId(),
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
     * @throws InvalidTransactionHistory
     * @throws RefundNotPossibleException
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function refundOrder(string $orderId, Amount $refundAmount): void
    {
        $transactionHistory = $this->getTransactionHistoryByOrderId($orderId);
        $this->validateRefundPossibility($transactionHistory, $refundAmount);

        /** @var ChargeHistoryItem[] $chargeItems */
        $chargeItems = $transactionHistory->collection()->chargeItems()->getAll();

        foreach ($chargeItems as $chargeItem) {
            if ($refundAmount->getValue() > $chargeItem->getRefundableAmount()->getValue()) {
                $this->unzerFactory->makeUnzerAPI()->cancelChargeById(
                    $transactionHistory->getPaymentId(),
                    $chargeItem->getId(),
                    $chargeItem->getRefundableAmount()->getPriceInCurrencyUnits()
                );
                $refundAmount = $refundAmount->minus($chargeItem->getRefundableAmount());

                continue;
            }

            $this->unzerFactory->makeUnzerAPI()->cancelChargeById(
                $transactionHistory->getPaymentId(),
                $chargeItem->getId(),
                $refundAmount->getPriceInCurrencyUnits()
            );

            break;
        }
    }

    /**
     * @param string $orderId
     *
     * @return TransactionHistory
     *
     * @throws InvalidTransactionHistory
     * @throws TransactionHistoryNotFoundException
     */
    private function getTransactionHistoryByOrderId(string $orderId): TransactionHistory
    {
        if (!$transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId)) {
            throw new TransactionHistoryNotFoundException(
                new TranslatableLabel(
                    "Transaction history for orderID:{$orderId} not found",
                    'transactionHistory.notFound')
            );
        }

        $this->validateTransactionHistory($transactionHistory);

        return $transactionHistory;
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return void
     *
     * @throws InvalidTransactionHistory
     */
    private function validateTransactionHistory(TransactionHistory $transactionHistory): void
    {
//        if (!$transactionHistory->getChargedAmount() ||
//            !$transactionHistory->getCancelledAmount() ||
//            !$transactionHistory->getTotalAmount() ||
//            !$transactionHistory->getRemainingAmount()) {
//            throw new InvalidTransactionHistory(
//                new TranslatableLabel(
//                    "Invalid amount for transaction history for orderID:{$transactionHistory->getOrderId()}.",
//                    'transactionHistory.invalidAmount')
//            );
//        }
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param Amount $amountToCharge
     *
     * @return void
     *
     * @throws ChargeNotPossibleException
     */
    private function validateChargePossibility(TransactionHistory $transactionHistory, Amount $amountToCharge): void
    {
        if (!$transactionHistory->getRemainingAmount()->getValue() ||
            $amountToCharge->getValue() > $transactionHistory->getRemainingAmount()->getValue()
        ) {
            throw new ChargeNotPossibleException(
                new TranslatableLabel(
                    "Charge for orderID:{$transactionHistory->getOrderId()} is not possible",
                    'charge.notPossible')
            );
        }
    }

    /**
     * Validates cancellation possibility.
     *
     * @param TransactionHistory $transactionHistory
     * @param Amount $amount
     *
     * @return void
     *
     * @throws CancellationNotPossibleException
     */
    private function validateCancellationPossibility(TransactionHistory $transactionHistory, Amount $amount): void
    {
        if (!$transactionHistory->getRemainingAmount()->getValue() ||
            $amount->getValue() > $transactionHistory->getRemainingAmount()->getValue()
        ) {
            throw new CancellationNotPossibleException(
                new TranslatableLabel(
                    "Cancellation for orderID:{$transactionHistory->getOrderId()} is not possible",
                    'cancellation.notPossible')
            );
        }
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param Amount $refundAmount
     *
     * @return void
     *
     * @throws RefundNotPossibleException
     * @throws CurrencyMismatchException
     */
    private function validateRefundPossibility(TransactionHistory $transactionHistory, Amount $refundAmount)
    {
        if (
            ($transactionHistory->getCancelledAmount()->plus($refundAmount))->getValue() > $transactionHistory->getChargedAmount()->getValue()
        ) {
            throw new RefundNotPossibleException(
                new TranslatableLabel(
                    "Refund for orderID:{$transactionHistory->getOrderId()} is not possible",
                    'refund.notPossible')
            );
        }
    }
}
