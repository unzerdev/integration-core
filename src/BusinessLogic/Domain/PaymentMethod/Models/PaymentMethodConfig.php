<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodNames;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidAmountsException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;

/**
 * Class PaymentMethod.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models
 */
class PaymentMethodConfig
{
    /**
     * Payment type extracted from Unzer API
     *
     * @var string
     */
    private string $type;

    /**
     * Is payment type enabled.
     *
     * @var bool
     */
    private bool $enabled;

    /**
     * @var BookingMethod
     */
    private BookingMethod $bookingMethod;

    /**
     * @var bool
     */
    private bool $sendBasketData = false;

    /**
     * @var ?TranslationCollection
     */
    private ?TranslationCollection $name;

    /**
     * @var ?TranslationCollection
     */
    private ?TranslationCollection $description;

    /**
     * Shop status ID. When order changes to this status, charge action is triggered.
     *
     * @var ?string
     */
    private ?string $statusIdToCharge;

    /**
     * @var ?Amount
     */
    private ?Amount $minOrderAmount;

    /**
     * @var ?Amount
     */
    private ?Amount $maxOrderAmount;

    /**
     * @var ?Amount
     */
    private ?Amount $surcharge;

    /**
     * @var Country[]
     */
    private array $restrictedCountries = [];

    /**
     * @var bool
     */
    private bool $enableClickToPay = false;

    /**
     * @param string $type
     * @param bool $enabled
     * @param BookingMethod $bookingMethod
     * @param bool $sendBasketData
     * @param ?TranslationCollection $name
     * @param ?TranslationCollection $description
     * @param string|null $statusIdToCharge
     * @param Amount|null $minOrderAmount
     * @param Amount|null $maxOrderAmount
     * @param Amount|null $surcharge
     * @param array $restrictedCountries
     * @param bool $enableClickToPay
     *
     * @throws InvalidAmountsException
     */
    public function __construct(
        string $type,
        bool $enabled,
        BookingMethod $bookingMethod,
        bool $sendBasketData = false,
        ?TranslationCollection $name = null,
        ?TranslationCollection $description = null,
        ?string $statusIdToCharge = null,
        ?Amount $minOrderAmount = null,
        ?Amount $maxOrderAmount = null,
        ?Amount $surcharge = null,
        array $restrictedCountries = [],
        bool $enableClickToPay = false
    ) {
        $this->validateAmounts($minOrderAmount, $maxOrderAmount);

        $this->type = $type;
        $this->enabled = $enabled;
        $this->bookingMethod = $bookingMethod;
        $this->sendBasketData = $sendBasketData;
        $this->name = $name;
        $this->description = $description;
        $this->statusIdToCharge = $statusIdToCharge;
        $this->minOrderAmount = $minOrderAmount;
        $this->maxOrderAmount = $maxOrderAmount;
        $this->surcharge = $surcharge;
        $this->restrictedCountries = $restrictedCountries;
        $this->enableClickToPay = $enableClickToPay;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return ?TranslationCollection
     */
    public function getName(): ?TranslationCollection
    {
        return $this->name;
    }

    /**
     * @return ?TranslationCollection
     */
    public function getDescription(): ?TranslationCollection
    {
        return $this->description;
    }

    /**
     * @return BookingMethod
     */
    public function getBookingMethod(): BookingMethod
    {
        return $this->bookingMethod;
    }

    /**
     * @return ?string
     */
    public function getStatusIdToCharge(): ?string
    {
        return $this->statusIdToCharge;
    }

    /**
     * @return ?Amount
     */
    public function getMinOrderAmount(): ?Amount
    {
        return $this->minOrderAmount;
    }

    /**
     * @return ?Amount
     */
    public function getMaxOrderAmount(): ?Amount
    {
        return $this->maxOrderAmount;
    }

    /**
     * @return ?Amount
     */
    public function getSurcharge(): ?Amount
    {
        return $this->surcharge;
    }

    /**
     * @return Country[]
     */
    public function getRestrictedCountries(): array
    {
        return $this->restrictedCountries;
    }

    /**
     * @return bool
     */
    public function isSendBasketData(): bool
    {
        return $this->sendBasketData;
    }

    /**
     * @return bool
     */
    public function isClickToPayEnabled(): bool
    {
        return $this->enableClickToPay;
    }

    /**
     * @param Amount|null $surcharge
     *
     * @return void
     */
    public function setSurcharge(?Amount $surcharge): void
    {
        $this->surcharge = $surcharge;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getNameByLocale(string $locale): string
    {
        if (!$this->name || empty($this->name->getTranslationMessage($locale))) {
            return PaymentMethodNames::PAYMENT_METHOD_NAMES[$this->type] ??
                PaymentMethodNames::DEFAULT_PAYMENT_METHOD_NAME . ' ' . $this->type;
        }

        return $this->name->getTranslationMessage($locale);
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescriptionByLocale(string $locale): string
    {
        if (!$this->description) {
            return PaymentMethodNames::DEFAULT_PAYMENT_METHOD_DESCRIPTION;
        }

        return $this->description->getTranslationMessage($locale);
    }

    /**
     * @param Amount|null $minAmount
     * @param Amount|null $maxAmount
     *
     * @return void
     * @throws InvalidAmountsException
     */
    private function validateAmounts(?Amount $minAmount, ?Amount $maxAmount): void
    {
        if (!$minAmount && !$maxAmount) {
            return;
        }

        if ($minAmount && !$maxAmount) {
            throw new InvalidAmountsException(
                new TranslatableLabel('Maximum amount is required.', 'paymentMethodConfig.maxAmountMissing')
            );
        }

        if (!$minAmount && $maxAmount) {
            throw new InvalidAmountsException(
                new TranslatableLabel('Minimum amount is required.', 'paymentMethodConfig.minAmountMissing')
            );
        }

        if ($minAmount->getValue() > $maxAmount->getValue()) {
            throw new InvalidAmountsException(
                new TranslatableLabel('Minimum amount can not be greater than maximum amount.',
                    'paymentMethodConfig.amountMismatch')
            );
        }
    }
}
