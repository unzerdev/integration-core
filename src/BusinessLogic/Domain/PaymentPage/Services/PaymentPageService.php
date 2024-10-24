<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
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

    /**
     * PaymentPageService constructor.
     * @param UnzerFactory $unzerFactory
     * @param PaymentMethodService $paymentMethodService
     * @param TransactionHistoryService $transactionHistoryService
     */
    public function __construct(
        UnzerFactory $unzerFactory,
        PaymentMethodService $paymentMethodService,
        TransactionHistoryService $transactionHistoryService
    ) {
        $this->unzerFactory = $unzerFactory;
        $this->paymentMethodService = $paymentMethodService;
        $this->transactionHistoryService = $transactionHistoryService;
    }

    /**
     * @throws ConnectionSettingsNotFoundException
     * @throws PaymentConfigNotFoundException
     * @throws UnzerApiException
     */
    public function create(string $paymentMethodType, string $orderId, Amount $amount, string $returnUrl): Paypage
    {
        $paymentMethodSettings = $this->paymentMethodService->getPaymentMethodConfigByType($paymentMethodType);
        if (!$paymentMethodSettings || !$paymentMethodSettings->isEnabled()) {
            throw new PaymentConfigNotFoundException(
                new TranslatableLabel(
                    "Enabled payment method config for type: $paymentMethodType not found",
                    'paymentMethod.configNotFound'
                ),
            );
        }

        $unzerApi = $this->unzerFactory->makeUnzerAPI();

        $payPageRequest = (new Paypage(
            $amount->getPriceInCurrencyUnits(),
            $amount->getCurrency()->getIsoCode(),
            $returnUrl
        ))->setExcludeTypes($this->getExcludePaymentTypesList(
            $unzerApi->fetchKeypair()->getAvailablePaymentTypes(),
            $paymentMethodType
        ))->setOrderId($orderId);

        if ($paymentMethodSettings->getBookingMethod()->equal(BookingMethod::authorize())) {
            $payPageResponse = $unzerApi->initPayPageAuthorize($payPageRequest);
        } else {
            $payPageResponse = $unzerApi->initPayPageCharge($payPageRequest);
        }

        $this->transactionHistoryService->saveTransactionHistory(
            new TransactionHistory($paymentMethodType, $payPageResponse->getPaymentId(), $orderId)
        );

        return $payPageResponse;
    }

    private function getExcludePaymentTypesList(array $availablePaymentTypes, string $selectedPaymentType): array
    {
        return array_values(array_filter(
            array_unique($availablePaymentTypes),
            static function ($paymentMethodType) use ($selectedPaymentType) {
                return $paymentMethodType !== $selectedPaymentType;
            }
        ));
    }
}
