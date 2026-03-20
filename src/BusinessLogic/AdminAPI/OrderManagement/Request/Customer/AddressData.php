<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\Customer;

use UnzerSDK\Resources\EmbeddedResources\Address;

/**
 * Class AddressData.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request
 */
class AddressData
{
    /** @var string|null */
    private ?string $name;

    /** @var string|null */
    private ?string $street;

    /** @var string|null */
    private ?string $state;

    /** @var string|null */
    private ?string $zip;

    /** @var string|null */
    private ?string $city;

    /** @var string|null */
    private ?string $country;

    /** @var string|null */
    private ?string $shippingType;

    /**
     * @param string|null $name
     * @param string|null $street
     * @param string|null $state
     * @param string|null $zip
     * @param string|null $city
     * @param string|null $country
     * @param string|null $shippingType
     */
    public function __construct(
        ?string $name = null,
        ?string $street = null,
        ?string $state = null,
        ?string $zip = null,
        ?string $city = null,
        ?string $country = null,
        ?string $shippingType = null
    ) {
        $this->name = $name;
        $this->street = $street;
        $this->state = $state;
        $this->zip = $zip;
        $this->city = $city;
        $this->country = $country;
        $this->shippingType = $shippingType;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->zip;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getShippingType(): ?string
    {
        return $this->shippingType;
    }

    /**
     * @return Address
     */
    public function toUnzerAddress(): Address
    {
        $address = new Address();
        $address->setName($this->name);
        $address->setStreet($this->street);
        $address->setState($this->state);
        $address->setZip($this->zip);
        $address->setCity($this->city);
        $address->setCountry($this->country);
        $address->setShippingType($this->shippingType);

        return $address;
    }
}
