<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\MetadataProvider;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory\BasketFactory;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory\CustomerFactory;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Factory\PaymentPageFactory;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Class PaymentPageService
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Services
 */
class PaymentPageService
{
    private UnzerFactory $unzerFactory;
    private PaymentMethodService $paymentMethodService;
    private TransactionHistoryService $transactionHistoryService;
    private PaymentPageFactory $paymentPageFactory;
    private CustomerFactory $customerFactory;
    private BasketFactory $basketFactory;
    private MetadataProvider $metadataProvider;

    /**
     * PaymentPageService constructor.
     *
     * @param UnzerFactory $unzerFactory
     * @param PaymentMethodService $paymentMethodService
     * @param TransactionHistoryService $transactionHistoryService
     * @param PaymentPageFactory $paymentPageFactory
     * @param CustomerFactory $customerFactory
     * @param BasketFactory $basketFactory
     * @param MetadataProvider $metadataProvider
     */
    public function __construct(
        UnzerFactory $unzerFactory,
        PaymentMethodService $paymentMethodService,
        TransactionHistoryService $transactionHistoryService,
        PaymentPageFactory $paymentPageFactory,
        CustomerFactory $customerFactory,
        BasketFactory $basketFactory,
        MetadataProvider $metadataProvider
    ) {
        $this->unzerFactory = $unzerFactory;
        $this->paymentMethodService = $paymentMethodService;
        $this->transactionHistoryService = $transactionHistoryService;
        $this->paymentPageFactory = $paymentPageFactory;
        $this->customerFactory = $customerFactory;
        $this->basketFactory = $basketFactory;
        $this->metadataProvider = $metadataProvider;
    }

    /**
     * @throws ConnectionSettingsNotFoundException
     * @throws PaymentConfigNotFoundException
     * @throws UnzerApiException
     */
    public function create(PaymentPageCreateContext $context): Paypage
    {
        $paymentMethodSettings = $this->getEnabledPaymentMethodSettings($context);

        $resources = $this->buildResources($context);

        $payPageResponse = $this->unzerFactory->makeUnzerAPI()->createPaypage(
            $this->paymentPageFactory->create($context, $paymentMethodSettings->getBookingMethod()->getBookingMethod(),
                $resources
            ),
        );

        $this->updateTransactionHistory($context);

        return $payPageResponse;
    }

    /**
     * @param PaymentPageCreateContext $context
     *
     * @return void
     */
    private function updateTransactionHistory(PaymentPageCreateContext $context) : void
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($context->getOrderId())
            ?? new TransactionHistory(
                $context->getPaymentMethodType(),
                null,
                $context->getOrderId(),
                $context->getAmount()->getCurrency()->getIsoCode()
            );

        $transactionHistory->setType($context->getPaymentMethodType());
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);
    }

    /**
     * @param PaymentPageCreateContext $context
     *
     * @return PaymentMethodConfig|null
     * @throws PaymentConfigNotFoundException
     */
    private function getEnabledPaymentMethodSettings(PaymentPageCreateContext $context): ?PaymentMethodConfig
    {
        $settings = $this->paymentMethodService->getPaymentMethodConfigByType($context->getPaymentMethodType());
        if (!$settings || !$settings->isEnabled()) {
            throw new PaymentConfigNotFoundException(
                new TranslatableLabel(
                    "Enabled payment method config for type: {$context->getPaymentMethodType()} not found",
                    'paymentMethod.configNotFound'
                ),
            );
        }
        return $settings;
    }

    /**
     * @param PaymentPageCreateContext $context
     *
     * @return Resources
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    private function buildResources(PaymentPageCreateContext $context) : Resources
    {
        $customer = $this->customerFactory->create($context);
        if ($customer !== null) {
            $customer = $this->unzerFactory->makeUnzerAPI()->createOrUpdateCustomer($customer);
        }

        $basket = $this->basketFactory->create($context);
        if($basket !== null) {
            $basket = $this->unzerFactory->makeUnzerAPI()->createBasket($basket);
        }

        $metaData = $this->metadataProvider->get($context);
        $metaData = $this->unzerFactory->makeUnzerAPI()->createMetadata($metaData);

        return new Resources(
            $customer !== null ? $customer->getId() : null,
            $basket !== null ? $basket->getId() : null,
            $metaData->getId()
        );
    }

    /**
     * @param string $orderId
     *
     * @return PaymentState
     * @throws ConnectionSettingsNotFoundException
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     * @throws InvalidCurrencyCode
     */
    public function getPaymentState(string $orderId): PaymentState
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId);
        if (!$transactionHistory) {
            throw new TransactionHistoryNotFoundException(new TranslatableLabel(
                "Transaction history for orderId: $orderId not found.", 'transactionHistory.notFound'
            ));
        }

        $payment = $this->unzerFactory->makeUnzerAPI()->fetchPayment($transactionHistory->getPaymentId());
        $newTransactionHistory = TransactionHistory::fromUnzerPayment($payment);
        if (!$newTransactionHistory->isEqual($transactionHistory)) {
            $newTransactionHistory->synchronizeHistoryItems($transactionHistory);
            $this->transactionHistoryService->saveTransactionHistory($newTransactionHistory);
        }

        return new PaymentState($payment->getState(), $payment->getStateName());
    }
}
