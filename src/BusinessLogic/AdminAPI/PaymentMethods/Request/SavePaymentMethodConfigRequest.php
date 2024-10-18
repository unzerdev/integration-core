<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Country\Exceptions\InvalidCountryArrayException;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidBookingMethodException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SavePaymentMethodConfigRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response
 */
class SavePaymentMethodConfigRequest extends Request
{
    /** @var string */
    private string $type;

    /** @var string */
    private string $bookingMethod;

    /** @var array */
    private array $name;

    /** @var array */
    private array $description;

    /** @var ?string */
    private ?string $statusIdToCharge = null;

    /** @var ?float */
    private ?float $minOrderAmount;

    /** @var ?float */
    private ?float $maxOrderAmount;

    /** @var ?float */
    private ?float $surcharge;

    /** @var array */
    private array $restrictedCountries;

    /** @var bool */
    private bool $sendBasketData;

    /**
     * @param string $type
     * @param array $name
     * @param array $description
     * @param ?string $bookingMethod
     * @param ?string $statusIdToCharge
     * @param ?float $minOrderAmount
     * @param ?float $maxOrderAmount
     * @param ?float $surcharge
     * @param array $restrictedCountries
     * @param bool $sendBasketData
     */
    public function __construct(
        string $type,
        string $bookingMethod,
        array $name = [],
        array $description = [],
        ?string $statusIdToCharge = null,
        ?float $minOrderAmount = null,
        ?float $maxOrderAmount = null,
        ?float $surcharge = null,
        array $restrictedCountries = [],
        bool $sendBasketData = false
    ) {
        $this->type = $type;
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
     * @param Currency $currency
     *
     * @return PaymentMethodConfig
     *
     * @throws InvalidBookingMethodException
     * @throws InvalidCountryArrayException
     * @throws InvalidTranslatableArrayException
     */
    public function toDomainModel(Currency $currency): PaymentMethodConfig
    {
        return new PaymentMethodConfig(
            $this->type,
            true,
            BookingMethod::parse($this->bookingMethod),
            $this->sendBasketData,
            TranslatableLabel::fromArrayToBatch($this->name),
            TranslatableLabel::fromArrayToBatch($this->description),
            $this->statusIdToCharge,
            $this->minOrderAmount ? Amount::fromFloat($this->minOrderAmount, $currency) : null,
            $this->maxOrderAmount ? Amount::fromFloat($this->maxOrderAmount, $currency) : null,
            $this->surcharge ? Amount::fromFloat($this->surcharge, $currency) : null,
            Country::fromArrayToBatch($this->restrictedCountries)
        );
    }
}
