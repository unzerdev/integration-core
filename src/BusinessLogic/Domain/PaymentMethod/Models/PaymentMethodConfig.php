<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodNames;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

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
     * @var TranslatableLabel[]
     */
    private array $name = [];

    /**
     * @var TranslatableLabel[]
     */
    private array $description = [];

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
     * @param string $type
     * @param bool $enabled
     * @param array $name
     * @param array $description
     * @param BookingMethod $bookingMethod
     * @param string|null $statusIdToCharge
     * @param Amount|null $minOrderAmount
     * @param Amount|null $maxOrderAmount
     * @param Amount|null $surcharge
     * @param array $restrictedCountries
     * @param bool $sendBasketData
     */
    public function __construct(
        string $type,
        bool $enabled,
        BookingMethod $bookingMethod,
        bool $sendBasketData = false,
        array $name = [],
        array $description = [],
        ?string $statusIdToCharge = null,
        ?Amount $minOrderAmount = null,
        ?Amount $maxOrderAmount = null,
        ?Amount $surcharge = null,
        array $restrictedCountries = []
    ) {
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
     * @return TranslatableLabel[]
     */
    public function getName(): array
    {
        return $this->name;
    }

    /**
     * @return TranslatableLabel[]
     */
    public function getDescription(): array
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
        foreach ($this->name as $name) {
            if ($name->getCode() === $locale) {
                return $name->getMessage();
            }
        }

        foreach ($this->name as $name) {
            if ($name->getCode() === 'default') {
                return $name->getMessage();
            }
        }

        return PaymentMethodNames::PAYMENT_METHOD_NAMES[$this->type] ??
            PaymentMethodNames::DEFAULT_PAYMENT_METHOD_NAME . ' ' . $this->type;
    }

    /**
     * @param string $locale
     *
     * @return ?string
     */
    public function getDescriptionByLocale(string $locale): ?string
    {
        foreach ($this->description as $name) {
            if ($name->getCode() === $locale) {
                return $name->getMessage();
            }
        }

        foreach ($this->description as $name) {
            if ($name->getCode() === 'default') {
                return $name->getMessage();
            }
        }

        return null;
    }
}
