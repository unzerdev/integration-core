<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\Currency\CurrencyServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\BasketRequired;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\BookingAuthorizeSupport;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\BookingChargeSupport;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodNames;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\UnsupportedPaymentTypes;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidAmountsException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class PaymentMethodService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Services
 */
class PaymentMethodService
{
    /**
     * @var UnzerFactory
     */
    private UnzerFactory $unzerFactory;

    /**
     * @var PaymentMethodConfigRepositoryInterface
     */
    private PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository;

    /**
     * @var CurrencyServiceInterface
     */
    private CurrencyServiceInterface $currencyService;

    /**
     * @param UnzerFactory $unzerFactory
     * @param PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository
     * @param CurrencyServiceInterface $currencyService
     */
    public function __construct(
        UnzerFactory $unzerFactory,
        PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository,
        CurrencyServiceInterface $currencyService
    ) {
        $this->unzerFactory = $unzerFactory;
        $this->paymentMethodConfigRepository = $paymentMethodConfigRepository;
        $this->currencyService = $currencyService;
    }

    /**
     * Gets all available payment methods for list of payment methods page.
     *
     * @return PaymentMethod[]
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function getAllPaymentMethods(): array
    {
        $keypair = $this->unzerFactory->makeUnzerAPI()->fetchKeypair();
        $availablePaymentTypes = array_unique($keypair->getAvailablePaymentTypes());
        $availablePaymentTypes = array_values(
            array_diff($availablePaymentTypes, UnsupportedPaymentTypes::UNSUPPORTED_METHOD_TYPES)
        );
        $configuredPaymentMethods = $this->paymentMethodConfigRepository->getPaymentMethodConfigs();

        return array_map(function ($availablePaymentType) use ($configuredPaymentMethods) {
            return new PaymentMethod(
                $availablePaymentType,
                PaymentMethodNames::PAYMENT_METHOD_NAMES[$availablePaymentType] ??
                PaymentMethodNames::DEFAULT_PAYMENT_METHOD_NAME . ' ' . $availablePaymentType,
                $this->isPaymentTypeEnabled($availablePaymentType, $configuredPaymentMethods)
            );
        }, $availablePaymentTypes);
    }

    /**
     * @param string $type
     * @param bool $isEnabled
     *
     * @return void
     *
     * @throws InvalidAmountsException
     */
    public function enablePaymentMethodConfig(string $type, bool $isEnabled): void
    {
        $paymentMethodConfig = $this->getPaymentMethodConfigByType($type);
        if (!$paymentMethodConfig) {
            $paymentMethodConfig =
                new PaymentMethodConfig(
                    $type,
                    $isEnabled,
                    $this->getBookingMethodForType($type),
                    in_array($type, BasketRequired::BASKET_REQUIRED)
                );
        }
        $paymentMethodConfig->setEnabled($isEnabled);
        $this->paymentMethodConfigRepository->savePaymentMethodConfig($paymentMethodConfig);
    }

    /**
     * @param string $type
     *
     * @return ?PaymentMethodConfig
     *
     * @throws
     */
    public function getPaymentMethodConfigByType(string $type): ?PaymentMethodConfig
    {
        return $this->paymentMethodConfigRepository->getPaymentMethodConfigByType($type);
    }

    /**
     * @param PaymentMethodConfig $paymentMethodConfig
     *
     * @return void
     */
    public function savePaymentMethodConfig(PaymentMethodConfig $paymentMethodConfig): void
    {
        $this->paymentMethodConfigRepository->savePaymentMethodConfig($paymentMethodConfig);
    }

    /**
     * @param Amount $orderAmount
     * @param string $billingCountryIso
     *
     * @return PaymentMethodConfig[]
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function getPaymentMethodsForCheckout(Amount $orderAmount, string $billingCountryIso): array
    {
        $paymentMethodConfigs = $this->getPaymentMethodsForCheckoutFromConfig($orderAmount, $billingCountryIso);
        if (empty($paymentMethodConfigs)) {
            return [];
        }

        $paymentTypesAvailable = $this->getPaymentMethodsForCheckoutFromAPI($orderAmount, $billingCountryIso);

        return array_filter($paymentMethodConfigs,
            function (PaymentMethodConfig $paymentMethod) use ($paymentTypesAvailable) {
                return in_array($paymentMethod->getType(), $paymentTypesAvailable, true);
            });
    }

    /**
     * @param string $type
     *
     * @return BookingMethod
     */
    private function getBookingMethodForType(string $type): BookingMethod
    {
        if (in_array($type, BookingAuthorizeSupport::SUPPORTS_AUTHORIZE) &&
            in_array($type, BookingChargeSupport::SUPPORTS_CHARGE)) {
            return BookingMethod::charge();
        }

        if (in_array($type, BookingAuthorizeSupport::SUPPORTS_AUTHORIZE)) {
            return BookingMethod::authorize();
        }

        return BookingMethod::charge();
    }

    /**
     * @param Amount $orderAmount
     * @param string $billingCountryIso
     *
     * @return array
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    private function getPaymentMethodsForCheckoutFromAPI(Amount $orderAmount, string $billingCountryIso): array
    {
        $keypair = $this->unzerFactory->makeUnzerAPI()->fetchKeypair(true);
        $paymentTypes = $keypair->getPaymentTypes();
        $typesAvailable = [];

        foreach ($paymentTypes as $paymentType) {
            if (
                !property_exists($paymentType, 'supports') ||
                !is_array($paymentType->supports) ||
                !isset ($paymentType->supports[0])
            ) {
                continue;
            }
            $countries = $paymentType->supports[0]->countries ?: [];
            $currencies = $paymentType->supports[0]->currency ?: [];

            if (!empty($countries) && !in_array($billingCountryIso, $countries)) {
                continue;
            }

            if (!empty($currencies) && !in_array($orderAmount->getCurrency()->getIsoCode(), $currencies)) {
                continue;
            }

            $typesAvailable[] = $paymentType->type;
        }

        return $typesAvailable;
    }

    /**
     * @param Amount $orderAmount
     * @param string $billingCountryIso
     *
     * @return PaymentMethodConfig[]
     */
    private function getPaymentMethodsForCheckoutFromConfig(Amount $orderAmount, string $billingCountryIso): array
    {
        $allPaymentMethods = $this->paymentMethodConfigRepository->getPaymentMethodConfigs();

        if (empty($allPaymentMethods)) {
            return [];
        }

        $currentContextCurrency = $orderAmount->getCurrency();

        return array_filter($allPaymentMethods, function ($paymentMethod) use (
            $billingCountryIso,
            $currentContextCurrency,
            $orderAmount
        ) {
            if (!$paymentMethod->isEnabled() || !$paymentMethod->getBookingMethod()) {
                return false;
            }

            $restrictedCountriesCodes = array_map(
                fn($country) => $country->getCode(),
                $paymentMethod->getRestrictedCountries()
            );
            if (in_array($billingCountryIso, $restrictedCountriesCodes)) {
                return false;
            }

            if ($paymentMethod->getSurcharge()) {
                $paymentMethod->setSurcharge(
                    $this->getAmountForCurrentContext($paymentMethod->getSurcharge(), $currentContextCurrency)
                );
            }

            if (!$paymentMethod->getMinOrderAmount() || !$paymentMethod->getMaxOrderAmount()) {
                return true;
            }

            $minOrderAmount = $this->getAmountForCurrentContext(
                $paymentMethod->getMinOrderAmount(),
                $currentContextCurrency
            );
            $maxOrderAmount = $this->getAmountForCurrentContext(
                $paymentMethod->getMaxOrderAmount(),
                $currentContextCurrency
            );

            if($maxOrderAmount->getValue() === 0 || $minOrderAmount->getValue() === 0) {
                return true;
            }

            return $orderAmount->getValue() > $minOrderAmount->getValue() && $orderAmount->getValue() < $maxOrderAmount->getValue();
        });
    }

    /**
     * @param Amount $orderAmountInDefaultCurrency
     * @param Currency $currentContextCurrency
     *
     * @return Amount
     */
    private function getAmountForCurrentContext(
        Amount $orderAmountInDefaultCurrency,
        Currency $currentContextCurrency
    ): Amount {
        if ($orderAmountInDefaultCurrency->getCurrency()->equal($currentContextCurrency)) {
            return $orderAmountInDefaultCurrency;
        }

        return $this->currencyService->convert($orderAmountInDefaultCurrency, $currentContextCurrency);
    }

    /**
     * Returns true if payment method is already saved in database and if it is enabled.
     *
     * @param string $type
     * @param array $paymentMethodConfigs
     *
     * @return bool
     */
    private function isPaymentTypeEnabled(
        string $type,
        array $paymentMethodConfigs
    ): bool {
        return !empty(array_filter($paymentMethodConfigs, function ($config) use ($type) {
            return $config->getType() === $type && $config->isEnabled();
        }));
    }
}
