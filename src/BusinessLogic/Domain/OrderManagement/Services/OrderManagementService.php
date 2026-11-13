<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\EncryptionFailedException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PrivateKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\ChargePaymentNotReadableException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\ChargeResult;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\ChargeSkipReason;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\RefundResult;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Models\RefundSkipReason;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\RefundViaPayment;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\ChargeHistoryItem;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\EmbeddedResources\Address;
use UnzerSDK\Resources\EmbeddedResources\CompanyInfo;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;

/**
 * Class OrderManagementService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\OrderManagement\Services
 */
class OrderManagementService
{
    /** @var UnzerFactory $unzerFactory */
    protected UnzerFactory $unzerFactory;

    /** @var TransactionHistoryService $transactionHistoryService */
    protected TransactionHistoryService $transactionHistoryService;

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
     * @param Amount|null $chargeAmount
     * @param string|null $reference
     *
     * @return ChargeResult The charge sent to Unzer, or the reason nothing was sent.
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws EncryptionFailedException
     * @throws PrivateKeyInvalidException
     * @throws UnzerApiException
     * @throws ChargePaymentNotReadableException When an integration's fetch hook cannot read the payment.
     */
    public function chargeOrder(string $orderId, ?Amount $chargeAmount, ?string $reference = null): ChargeResult
    {
        if (!($transactionHistory = $this->fetchTransactionHistoryForCharge($orderId))) {
            return ChargeResult::skipped(ChargeSkipReason::PAYMENT_NOT_FOUND, null);
        }

        $skipReason = $this->getChargeSkipReason($transactionHistory, $chargeAmount);

        if ($skipReason !== null) {
            return ChargeResult::skipped($skipReason, $transactionHistory);
        }

        if ($chargeAmount === null) {
            $chargeAmount = $transactionHistory->getTotalAmount();
        }

        $authorizedItem = $transactionHistory->collection()->authorizedItem();

        if (!$authorizedItem) {
            return ChargeResult::skipped(ChargeSkipReason::NO_AUTHORIZATION, $transactionHistory);
        }

        $charge = new Charge($chargeAmount->getPriceInCurrencyUnits(), $chargeAmount->getCurrency()->getIsoCode());
        $charge->setOrderId($transactionHistory->getOrderId());
        $charge->setPaymentReference($reference);

        $charge = $this->unzerFactory->makeUnzerAPI()->performChargeOnPayment(
            $authorizedItem->getPaymentId(),
            $charge
        );

        return ChargeResult::charged($charge, $transactionHistory);
    }

    /**
     * Reads the transaction history a charge decides on. Integrations override it to read the payment
     * differently, e.g. to throw ChargePaymentNotReadableException instead of returning null on an Unzer error.
     *
     * @param string $orderId
     *
     * @return TransactionHistory|null
     *
     * @throws ChargePaymentNotReadableException
     */
    protected function fetchTransactionHistoryForCharge(string $orderId): ?TransactionHistory
    {
        return $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId);
    }

    /**
     * Decides whether a charge is sent to Unzer. Null means charge; otherwise one of the ChargeSkipReason
     * constants. The checks are the ones of isChargeNecessary(), each with its own reason; an integration
     * override of isChargeNecessary() still applies and maps to NOT_NECESSARY.
     *
     * @param TransactionHistory $transactionHistory
     * @param Amount|null $amountToCharge Null charges the total amount.
     *
     * @return string|null
     */
    protected function getChargeSkipReason(TransactionHistory $transactionHistory, ?Amount $amountToCharge): ?string
    {
        if ($amountToCharge === null) {
            // A full charge has no amount checks of its own; an integration override may still decline it.
            return $this->isChargeNecessary($transactionHistory, null) ? null : ChargeSkipReason::NOT_NECESSARY;
        }

        if (!$this->isTransactionHistoryValid($transactionHistory)) {
            return ChargeSkipReason::INVALID_HISTORY;
        }

        $stateId = $transactionHistory->getPaymentState()->getId();

        if ($stateId === PaymentState::STATE_CANCELED) {
            return ChargeSkipReason::PAYMENT_CANCELED;
        }

        if ($stateId === PaymentState::STATE_CREATE) {
            return ChargeSkipReason::PAYMENT_NOT_CREATED;
        }

        $remainingAmount = $transactionHistory->getRemainingAmount();

        if (!$remainingAmount || !$remainingAmount->getValue()) {
            return ChargeSkipReason::NOTHING_REMAINING;
        }

        if ($amountToCharge->getValue() > $remainingAmount->getValue()) {
            return ChargeSkipReason::AMOUNT_EXCEEDS_REMAINING;
        }

        return $this->isChargeNecessary($transactionHistory, $amountToCharge)
            ? null
            : ChargeSkipReason::NOT_NECESSARY;
    }

    /**
     * @param string $orderId
     * @param Amount|null $amount
     * @param string|null $reference
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws EncryptionFailedException
     * @throws PrivateKeyInvalidException
     * @throws UnzerApiException
     */
    public function cancelOrder(string $orderId, ?Amount $amount = null, ?string $reference = null): void
    {
        if (!($transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId))) {
            return;
        }

        if (!$this->isCancellationNecessary($transactionHistory, $amount)) {
            return;
        }

        if ($amount === null) {
            $amount = $transactionHistory->getTotalAmount();
        }

        if (in_array($transactionHistory->getType(), PaymentMethodTypes::UPL_TYPES)) {
            $cancellation = new Cancellation($amount->getPriceInCurrencyUnits());
            $cancellation->setPaymentReference($reference);

            $this->unzerFactory->makeUnzerAPI()->cancelAuthorizedPayment($orderId, $cancellation);

            return;
        }

        $authorizedItem = $transactionHistory->collection()->authorizedItem();

        if (!$authorizedItem) {
            return;
        }

        $cancellation = new Cancellation($amount->getPriceInCurrencyUnits());
        $cancellation->setPaymentReference($reference);

        $this->unzerFactory->makeUnzerAPI()->cancelAuthorizedPayment(
            $authorizedItem->getPaymentId(),
            $cancellation
        );
    }

    /**
     * Refunds an amount of the order at Unzer: through the payment for the refund-via-payment methods, otherwise
     * charge by charge.
     *
     * @param string $orderId
     * @param Amount $refundAmount
     * @param string|null $reference
     *
     * @return RefundResult The cancellations sent to Unzer, the reason nothing was sent, or the cancellations made
     *                      before Unzer failed on a later charge.
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws CurrencyMismatchException
     * @throws UnzerApiException When the first cancellation fails: nothing was refunded.
     * @throws EncryptionFailedException
     * @throws PrivateKeyInvalidException
     */
    public function refundOrder(string $orderId, Amount $refundAmount, ?string $reference = null): RefundResult
    {
        if (!($transactionHistory = $this->fetchTransactionHistoryForRefund($orderId))) {
            return RefundResult::skipped(RefundSkipReason::PAYMENT_NOT_FOUND, null);
        }

        $skipReason = $this->getRefundSkipReason($transactionHistory, $refundAmount);

        if ($skipReason !== null) {
            return RefundResult::skipped($skipReason, $transactionHistory);
        }

        if (in_array($transactionHistory->getType(), RefundViaPayment::REFUND_VIA_PAYMENT, true)) {
            return RefundResult::refunded(
                [$this->refundOrderByPayment($transactionHistory, $refundAmount, $reference)],
                $transactionHistory
            );
        }

        /** @var ChargeHistoryItem[] $chargeItems */
        $chargeItems = $transactionHistory->collection()->chargeItems()->getAll();
        $cancellations = [];

        foreach ($chargeItems as $chargeItem) {
            if (!$chargeItem->isSuccessful() || $chargeItem->getRefundableAmount()->getValue() <= 0) {
                continue;
            }

            $amountOnThisCharge = $refundAmount->getValue() > $chargeItem->getRefundableAmount()->getValue()
                ? $chargeItem->getRefundableAmount()
                : $refundAmount;

            try {
                $cancellations[] = $this->unzerFactory->makeUnzerAPI()->cancelChargeById(
                    $chargeItem->getPaymentId(),
                    $chargeItem->getId(),
                    $amountOnThisCharge->getPriceInCurrencyUnits(),
                    null,
                    $reference
                );
            } catch (UnzerApiException $exception) {
                if ($cancellations === []) {
                    // Nothing was refunded: the caller sees the Unzer error, as before.
                    throw $exception;
                }

                return RefundResult::partial($cancellations, $exception, $transactionHistory);
            }

            if ($amountOnThisCharge === $refundAmount) {
                break;
            }

            $refundAmount = $refundAmount->minus($amountOnThisCharge);
        }

        return RefundResult::refunded($cancellations, $transactionHistory);
    }

    /**
     * Reads the transaction history a refund decides on. Integrations override it to read the payment differently,
     * e.g. to keep the payment for their own checks.
     *
     * @param string $orderId
     *
     * @return TransactionHistory|null
     */
    protected function fetchTransactionHistoryForRefund(string $orderId): ?TransactionHistory
    {
        return $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId);
    }

    /**
     * Decides whether a refund is sent to Unzer. Null means refund; otherwise one of the RefundSkipReason
     * constants. An integration override of isRefundNecessary() still applies and maps to NOT_NECESSARY.
     *
     * @param TransactionHistory $transactionHistory
     * @param Amount $amountToRefund
     *
     * @return string|null
     *
     * @throws CurrencyMismatchException
     */
    protected function getRefundSkipReason(TransactionHistory $transactionHistory, Amount $amountToRefund): ?string
    {
        if (!$this->isTransactionHistoryValid($transactionHistory)) {
            return RefundSkipReason::INVALID_HISTORY;
        }

        $stateId = $transactionHistory->getPaymentState()->getId();

        if ($stateId === PaymentState::STATE_PENDING) {
            return RefundSkipReason::PAYMENT_PENDING;
        }

        if ($stateId === PaymentState::STATE_CANCELED) {
            return RefundSkipReason::PAYMENT_CANCELED;
        }

        if ($stateId === PaymentState::STATE_CREATE) {
            return RefundSkipReason::PAYMENT_NOT_CREATED;
        }

        if ($amountToRefund->getValue() > $this->getRefundableAmount($transactionHistory)->getValue()) {
            return RefundSkipReason::AMOUNT_EXCEEDS_REFUNDABLE;
        }

        return $this->isRefundNecessary($transactionHistory, $amountToRefund)
            ? null
            : RefundSkipReason::NOT_NECESSARY;
    }

    /**
     * What is still refundable: the successful charges minus what was already refunded on them.
     * Expects a history that passed isTransactionHistoryValid() (it takes the currency from the total amount).
     *
     * @param TransactionHistory $transactionHistory
     *
     * @return Amount
     *
     * @throws CurrencyMismatchException
     */
    protected function getRefundableAmount(TransactionHistory $transactionHistory): Amount
    {
        $refundable = Amount::fromInt(0, $transactionHistory->getTotalAmount()->getCurrency());

        /** @var ChargeHistoryItem $chargeItem */
        foreach ($transactionHistory->collection()->chargeItems()->getAll() as $chargeItem) {
            if ($chargeItem->isSuccessful() && $chargeItem->getRefundableAmount()->getValue() > 0) {
                $refundable = $refundable->plus($chargeItem->getRefundableAmount());
            }
        }

        return $refundable;
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param Amount $refundAmount
     * @param string|null $reference
     *
     * @return Cancellation The cancellation Unzer created.
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws EncryptionFailedException
     * @throws PrivateKeyInvalidException
     * @throws UnzerApiException
     */
    public function refundOrderByPayment(
        TransactionHistory $transactionHistory,
        Amount $refundAmount,
        ?string $reference = null
    ): Cancellation {
        $paymentId = $transactionHistory->collection()->last()->getPaymentId();
        $cancellation = new Cancellation($refundAmount->getPriceInCurrencyUnits());
        $cancellation->setPaymentReference($reference);

        return $this->unzerFactory->makeUnzerAPI()->cancelChargedPayment(
            $paymentId,
            $cancellation
        );
    }

    /**
     * @param string $orderId
     * @param Customer $customer
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws EncryptionFailedException
     * @throws PrivateKeyInvalidException
     * @throws UnzerApiException
     */
    public function updateCustomer(string $orderId, Customer $customer): void
    {
        $unzer = $this->unzerFactory->makeUnzerAPI();

        if ($customer->getId() !== null) {
            $unzer->updateCustomer($customer);

            return;
        }

        $transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId);
        if (!$transactionHistory) {
            return;
        }

        $payment = $unzer->fetchPayment($transactionHistory->getOrderId());
        $existingCustomer = $payment->getCustomer();
        if (!$existingCustomer) {
            return;
        }

        $this->applyCustomerData($existingCustomer, $customer);
        $unzer->updateCustomer($existingCustomer);
    }

    /**
     * @param Customer $existing
     * @param Customer $updates
     *
     * @return void
     */
    protected function applyCustomerData(Customer $existing, Customer $updates): void
    {
        $existing->setFirstname($updates->getFirstname() ?? $existing->getFirstname());
        $existing->setLastname($updates->getLastname() ?? $existing->getLastname());
        $existing->setBirthDate($updates->getBirthDate() ?? $existing->getBirthDate());
        $existing->setCompany($updates->getCompany() ?? $existing->getCompany());
        $existing->setEmail($updates->getEmail() ?? $existing->getEmail());
        $existing->setPhone($updates->getPhone() ?? $existing->getPhone());
        $existing->setMobile($updates->getMobile() ?? $existing->getMobile());

        if ($updates->getSalutation() !== 'unknown') {
            $existing->setSalutation($updates->getSalutation());
        }

        try {
            $language = $updates->getLanguage();
            if ($language !== '') {
                $existing->setLanguage($language);
            }
        } catch (\TypeError $e) {
        }

        $this->applyAddressData($existing->getBillingAddress(), $updates->getBillingAddress());
        $this->applyAddressData($existing->getShippingAddress(), $updates->getShippingAddress());
        $this->applyCompanyInfoData($existing, $updates);
    }

    /**
     * @param Address $existing
     * @param Address $updates
     *
     * @return void
     */
    protected function applyAddressData(Address $existing, Address $updates): void
    {
        $existing->setName($updates->getName() ?? $existing->getName());
        $existing->setStreet($updates->getStreet() ?? $existing->getStreet());
        $existing->setState($updates->getState() ?? $existing->getState());
        $existing->setZip($updates->getZip() ?? $existing->getZip());
        $existing->setCity($updates->getCity() ?? $existing->getCity());
        $existing->setCountry($updates->getCountry() ?? $existing->getCountry());
        $existing->setShippingType($updates->getShippingType() ?? $existing->getShippingType());
    }

    /**
     * @param Customer $existing
     * @param Customer $updates
     *
     * @return void
     */
    protected function applyCompanyInfoData(Customer $existing, Customer $updates): void
    {
        if ($updates->getCompanyInfo() === null) {
            return;
        }

        $existingInfo = $existing->getCompanyInfo() ?? new CompanyInfo();
        $updatesInfo = $updates->getCompanyInfo();

        $existingInfo->setRegistrationType($updatesInfo->getRegistrationType() ?? $existingInfo->getRegistrationType());
        $existingInfo->setCommercialRegisterNumber($updatesInfo->getCommercialRegisterNumber() ?? $existingInfo->getCommercialRegisterNumber());
        $existingInfo->setFunction($updatesInfo->getFunction() ?? $existingInfo->getFunction());
        $existingInfo->setCompanyType($updatesInfo->getCompanyType() ?? $existingInfo->getCompanyType());

        if ($updatesInfo->getCommercialSector() !== 'OTHER') {
            $existingInfo->setCommercialSector($updatesInfo->getCommercialSector());
        }

        if ($updatesInfo->getOwner() !== null) {
            $existingOwner = $existingInfo->getOwner();

            if ($existingOwner === null) {
                $existingInfo->setOwner($updatesInfo->getOwner());
            } else {
                $existingOwner->setFirstname($updatesInfo->getOwner()->getFirstname() ?? $existingOwner->getFirstname());
                $existingOwner->setLastname($updatesInfo->getOwner()->getLastname() ?? $existingOwner->getLastname());
                $existingOwner->setBirthdate($updatesInfo->getOwner()->getBirthdate() ?? $existingOwner->getBirthdate());
            }
        }

        $existing->setCompanyInfo($existingInfo);
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return bool
     */
    protected function isTransactionHistoryValid(TransactionHistory $transactionHistory): bool
    {
        return $transactionHistory->getChargedAmount() &&
            $transactionHistory->getCancelledAmount() &&
            $transactionHistory->getTotalAmount() &&
            $transactionHistory->getRemainingAmount() &&
            $transactionHistory->getPaymentState();
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param ?Amount $amountToCharge
     *
     * @return bool
     */
    protected function isChargeNecessary(TransactionHistory $transactionHistory, ?Amount $amountToCharge): bool
    {
        if ($amountToCharge === null) {
            return true;
        }

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
    protected function isCancellationNecessary(TransactionHistory $transactionHistory, ?Amount $amount = null): bool
    {
        if ($amount === null) {
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
     */
    protected function isRefundNecessary(TransactionHistory $transactionHistory, Amount $amountToRefund): bool
    {
        return $this->isTransactionHistoryValid($transactionHistory) &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_PENDING &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_CANCELED &&
            $transactionHistory->getPaymentState()->getId() !== PaymentState::STATE_CREATE &&
            $amountToRefund->getValue() <= $transactionHistory->getChargedAmount()->getValue();
    }
}
