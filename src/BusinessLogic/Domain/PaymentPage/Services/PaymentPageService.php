<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
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

    /**
     * PaymentPageService constructor.
     * @param UnzerFactory $unzerFactory
     * @param PaymentMethodService $paymentMethodService
     * @param TransactionHistoryService $transactionHistoryService
     * @param PaymentPageFactory $paymentPageFactory
     */
    public function __construct(
        UnzerFactory $unzerFactory,
        PaymentMethodService $paymentMethodService,
        TransactionHistoryService $transactionHistoryService,
        PaymentPageFactory $paymentPageFactory
    ) {
        $this->unzerFactory = $unzerFactory;
        $this->paymentMethodService = $paymentMethodService;
        $this->transactionHistoryService = $transactionHistoryService;
        $this->paymentPageFactory = $paymentPageFactory;
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

        $payPageRequest = $this->paymentPageFactory->crate($context);

        if ($paymentMethodSettings->getBookingMethod()->equal(BookingMethod::authorize())) {
            $payPageResponse = $this->unzerFactory->makeUnzerAPI()->initPayPageAuthorize($payPageRequest);
        } else {
            $payPageResponse = $this->unzerFactory->makeUnzerAPI()->initPayPageCharge($payPageRequest);
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
