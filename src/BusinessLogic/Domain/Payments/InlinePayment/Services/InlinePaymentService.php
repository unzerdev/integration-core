<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Services;

use Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Response\InlinePaymentResponse;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
use Unzer\Core\BusinessLogic\Domain\Payments\Customer\Factory\CustomerFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Factory\InlinePaymentFactory;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePaymentCreateContext;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models\InlinePayment;
use Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Strategy\InlinePaymentStrategyFactory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;

/**
 * Class PaymentPageService
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPage\Services
 */
class InlinePaymentService
{
    private UnzerFactory $unzerFactory;
    private InlinePaymentStrategyFactory $inlinePaymentStrategyFactory;
    private PaymentMethodService $paymentMethodService;
    private TransactionHistoryService $transactionHistoryService;
    private InlinePaymentFactory $inlinePaymentFactory;
    private CustomerFactory $customerFactory;

    /**
     * PaymentPageService constructor.
     *
     * @param UnzerFactory $unzerFactory
     * @param InlinePaymentStrategyFactory $inlinePaymentStrategyFactory
     * @param PaymentMethodService $paymentMethodService
     * @param TransactionHistoryService $transactionHistoryService
     * @param InlinePaymentFactory $inlinePaymentFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        UnzerFactory                 $unzerFactory,
        InlinePaymentStrategyFactory $inlinePaymentStrategyFactory,
        PaymentMethodService         $paymentMethodService,
        TransactionHistoryService    $transactionHistoryService,
        InlinePaymentFactory         $inlinePaymentFactory,
        CustomerFactory              $customerFactory
    )
    {
        $this->unzerFactory = $unzerFactory;
        $this->inlinePaymentStrategyFactory = $inlinePaymentStrategyFactory;
        $this->paymentMethodService = $paymentMethodService;
        $this->transactionHistoryService = $transactionHistoryService;
        $this->inlinePaymentFactory = $inlinePaymentFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @throws ConnectionSettingsNotFoundException
     * @throws PaymentConfigNotFoundException
     * @throws UnzerApiException
     */
    public function create(InlinePaymentCreateContext $context): InlinePaymentResponse
    {
        $paymentMethodSettings = $this->getEnabledPaymentMethodSettings($context);
        $resources = $this->buildResources($context);
        $method = $paymentMethodSettings->getBookingMethod()->getBookingMethod();

        $inlinePayment = $this->inlinePaymentStrategyFactory
            ->makeStrategy($method, $this->unzerFactory, $this->inlinePaymentFactory)
            ->execute($context, $paymentMethodSettings, $resources);

        $this->updateTransactionHistory($context, $inlinePayment);

        return new InlinePaymentResponse($inlinePayment);
    }

    /**
     * @param InlinePaymentCreateContext $context
     * @param InlinePayment $response
     * @return void
     * @throws InvalidCurrencyCode
     */
    private function updateTransactionHistory(InlinePaymentCreateContext $context, InlinePayment $response): void
    {
        $payment = $response->getPayment();

        $fallbackTransactionHistory = $payment ?
            TransactionHistory::fromUnzerPayment($payment) :
            new TransactionHistory(
                $context->getPaymentMethodType(),
                $context->getOrderId(),
                $context->getAmount()->getCurrency()->getIsoCode(),);
        $transactionHistory = $this->transactionHistoryService->getTransactionHistoryByOrderId($context->getOrderId())
            ?? $fallbackTransactionHistory;

        $transactionHistory->setType($context->getPaymentMethodType());
        $this->transactionHistoryService->saveTransactionHistory($transactionHistory);
    }

    /**
     * @param InlinePaymentCreateContext $context
     *
     * @return PaymentMethodConfig
     * @throws PaymentConfigNotFoundException
     */
    private function getEnabledPaymentMethodSettings(InlinePaymentCreateContext $context): PaymentMethodConfig
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
     * @param InlinePaymentCreateContext $context
     *
     * @return Resources
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    private function buildResources(InlinePaymentCreateContext $context): Resources
    {
        $customer = $this->customerFactory->create($context);
        if ($customer !== null) {
            $customer = $this->unzerFactory->makeUnzerAPI()->createOrUpdateCustomer($customer);
        }

        return new Resources($customer !== null ? $customer->getId() : null,);
    }
}
