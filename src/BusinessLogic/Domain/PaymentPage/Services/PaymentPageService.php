<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPage\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidPaymentTypeException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentConfigNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodService;
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

    /**
     * PaymentPageService constructor.
     * @param UnzerFactory $unzerFactory
     */
    public function __construct(UnzerFactory $unzerFactory, PaymentMethodService $paymentMethodService)
    {
        $this->unzerFactory = $unzerFactory;
        $this->paymentMethodService = $paymentMethodService;
    }

    /**
     * @param string $paymentMethodType
     * @param Amount $amount
     * @param string $returnUrl
     * @return Paypage
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidPaymentTypeException
     * @throws PaymentConfigNotFoundException
     * @throws UnzerApiException
     */
    public function create(string $paymentMethodType, Amount $amount, string $returnUrl): Paypage
    {
        $paymentMethodSettings = $this->paymentMethodService->getPaymentMethodConfigByType($paymentMethodType);
        if (!$paymentMethodSettings->isEnabled()) {
            throw new PaymentConfigNotFoundException(
                new TranslatableLabel(
                    "Enabled pPayment method config for type: $paymentMethodType not found",
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
        ));

        if ($paymentMethodSettings->getBookingMethod()->equal(BookingMethod::authorize())) {
            return $unzerApi->initPayPageAuthorize($payPageRequest);
        }

        return $unzerApi->initPayPageCharge($payPageRequest);
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
