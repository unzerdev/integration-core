<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models;

use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
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
     * @var TranslatableLabel[]
     */
    private array $name = [];

    /**
     * @var TranslatableLabel[]
     */
    private array $description = [];

    /**
     * @var ?BookingMethod
     */
    private ?BookingMethod $bookingMethod = null;

    /**
     * Shop status ID. When order changes to this status, charge action is triggered.
     *
     * @var ?string
     */
    private ?string $statusIdToCharge = null;

    /**
     * @var ?float
     */
    private ?float $minOrderAmount = 0.0;

    /**
     * @var ?float
     */
    private ?float $maxOrderAmount = 0.0;

    /**
     * @var ?float
     */
    private ?float $surcharge = 0.0;

    /**
     * @var Country[]
     */
    private array $restrictedCountries = [];

    /**
     * @var bool
     */
    private bool $sendBasketData = false;

    /**
     * @param string $type
     * @param bool $enabled
     * @param array $name
     * @param array $description
     * @param BookingMethod|null $bookingMethod
     * @param string|null $statusIdToCharge
     * @param float|null $minOrderAmount
     * @param float|null $maxOrderAmount
     * @param float|null $surcharge
     * @param array $restrictedCountries
     * @param bool $sendBasketData
     */
    public function __construct(
        string $type,
        bool $enabled,
        array $name = [],
        array $description = [],
        ?BookingMethod $bookingMethod = null,
        ?string $statusIdToCharge = null,
        ?float $minOrderAmount = null,
        ?float $maxOrderAmount = null,
        ?float $surcharge = null,
        array $restrictedCountries = [],
        bool $sendBasketData = false
    ) {
        $this->type = $type;
        $this->enabled = $enabled;
        $this->name = $name;
        $this->description = $description;
        $this->bookingMethod = $bookingMethod;
        $this->statusIdToCharge = $statusIdToCharge;
        $this->minOrderAmount = $minOrderAmount;
        $this->maxOrderAmount = $maxOrderAmount;
        $this->surcharge = $surcharge;
        $this->restrictedCountries = $restrictedCountries;
        $this->sendBasketData = $sendBasketData;
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
     * @return ?BookingMethod
     */
    public function getBookingMethod(): ?BookingMethod
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
     * @return ?float
     */
    public function getMinOrderAmount(): ?float
    {
        return $this->minOrderAmount;
    }

    /**
     * @return ?float
     */
    public function getMaxOrderAmount(): ?float
    {
        return $this->maxOrderAmount;
    }

    /**
     * @return ?float
     */
    public function getSurcharge(): ?float
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
}
