<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\MetadataProvider;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
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
use UnzerSDK\Resources\PaymentTypes\Paypage;

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
        $paymentMethodSettings = $this->paymentMethodService->getPaymentMethodConfigByType($context->getPaymentMethodType());
        if (!$paymentMethodSettings || !$paymentMethodSettings->isEnabled()) {
            throw new PaymentConfigNotFoundException(
                new TranslatableLabel(
                    "Enabled payment method config for type: {$context->getPaymentMethodType()} not found",
                    'paymentMethod.configNotFound'
                ),
            );
        }

        $customer = $this->customerFactory->create($context);
        if ($customer !== null) {
            $customer = $this->unzerFactory->makeUnzerAPI()->createOrUpdateCustomer($customer);
        }

        if ($paymentMethodSettings->getBookingMethod()->equal(BookingMethod::authorize())) {
            $payPageResponse = $this->unzerFactory->makeUnzerAPI()->initPayPageAuthorize(
                $this->paymentPageFactory->create($context),
                $customer,
                $this->basketFactory->create($context),
                $this->metadataProvider->get($context)
            );
        } else {
            $payPageResponse = $this->unzerFactory->makeUnzerAPI()->initPayPageCharge(
                $this->paymentPageFactory->create($context),
                $customer,
                $this->basketFactory->create($context),
                $this->metadataProvider->get($context)
            );
        }

        $this->transactionHistoryService->saveTransactionHistory(
            new TransactionHistory($context->getPaymentMethodType(), $payPageResponse->getPaymentId(), $context->getOrderId())
        );

        return $payPageResponse;
    }

    public function getPaymentState(string $orderId): PaymentState
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($orderId);
        if (!$transactionHistory) {
            throw new TransactionHistoryNotFoundException(new TranslatableLabel(
                "Transaction history for orderId: $orderId not found.", 'transactionHistory.notFound'
            ));
        }

        $payment = $this->unzerFactory->makeUnzerAPI()->fetchPayment($transactionHistory->getPaymentId());

        return new PaymentState($payment->getState(), $payment->getStateName());
    }
}
