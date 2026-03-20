<?php

namespace Unzer\Core\BusinessLogic\Domain\OrderManagement\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
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
     * @param ?Amount $chargeAmount
     * @param string|null $reference
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function chargeOrder(string $orderId, ?Amount $chargeAmount, ?string $reference = null): void
    {
        if (!($transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId))) {
            return;
        }

        if (!$this->isChargeNecessary($transactionHistory, $chargeAmount)) {
            return;
        }

        if ($chargeAmount === null) {
            $chargeAmount = $transactionHistory->getTotalAmount();
        }

        $authorizedItem = $transactionHistory->collection()->authorizedItem();

        if (!$authorizedItem) {
            return;
        }

        $charge = new Charge($chargeAmount->getPriceInCurrencyUnits(), $chargeAmount->getCurrency()->getIsoCode());
        $charge->setOrderId($transactionHistory->getOrderId());
        $charge->setPaymentReference($reference);

        $this->unzerFactory->makeUnzerAPI()->performChargeOnPayment(
            $authorizedItem->getPaymentId(),
            $charge
        );
    }

    /**
     * @param string $orderId
     * @param Amount|null $amount
     * @param string|null $reference
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
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
     * @param string $orderId
     * @param Amount $refundAmount
     * @param string|null $reference
     *
     * @return void
     * @throws ConnectionSettingsNotFoundException
     * @throws CurrencyMismatchException
     * @throws UnzerApiException
     */
    public function refundOrder(string $orderId, Amount $refundAmount, ?string $reference = null): void
    {
        if (!($transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId))) {
            return;
        }

        if (!$this->isRefundNecessary($transactionHistory, $refundAmount)) {
            return;
        }

        if (in_array($transactionHistory->getType(), RefundViaPayment::REFUND_VIA_PAYMENT, true)) {
            $this->refundOrderByPayment($transactionHistory, $refundAmount, $reference);

            return;
        }

        /** @var ChargeHistoryItem[] $chargeItems */
        $chargeItems = $transactionHistory->collection()->chargeItems()->getAll();

        foreach ($chargeItems as $chargeItem) {
            if ($refundAmount->getValue() > $chargeItem->getRefundableAmount()->getValue()) {
                $this->unzerFactory->makeUnzerAPI()->cancelChargeById(
                    $chargeItem->getPaymentId(),
                    $chargeItem->getId(),
                    $chargeItem->getRefundableAmount()->getPriceInCurrencyUnits(),
                    null,
                    $reference
                );
                $refundAmount = $refundAmount->minus($chargeItem->getRefundableAmount());

                continue;
            }

            $this->unzerFactory->makeUnzerAPI()->cancelChargeById(
                $chargeItem->getPaymentId(),
                $chargeItem->getId(),
                $refundAmount->getPriceInCurrencyUnits(),
                null,
                $reference
            );

            break;
        }
    }

    /**
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function refundOrderByPayment(
        TransactionHistory $transactionHistory,
        Amount $refundAmount,
        ?string $reference = null
    ) {
        $paymentId = $transactionHistory->collection()->last()->getPaymentId();
        $cancellation = new Cancellation($refundAmount->getPriceInCurrencyUnits());
        $cancellation->setPaymentReference($reference);

        $this->unzerFactory->makeUnzerAPI()->cancelChargedPayment(
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
