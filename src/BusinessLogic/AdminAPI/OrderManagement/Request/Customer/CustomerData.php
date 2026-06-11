<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\Customer;

use UnzerSDK\Resources\Customer;

/**
 * Class CustomerData.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\Customer
 */
class CustomerData
{
    /** @var string|null */
    private ?string $firstname;

    /** @var string|null */
    private ?string $lastname;

    /** @var string|null */
    private ?string $salutation;

    /** @var string|null */
    private ?string $birthDate;

    /** @var string|null */
    private ?string $company;

    /** @var string|null */
    private ?string $email;

    /** @var string|null */
    private ?string $phone;

    /** @var string|null */
    private ?string $mobile;

    /** @var string|null */
    private ?string $language;

    /**
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $salutation
     * @param string|null $birthDate
     * @param string|null $company
     * @param string|null $email
     * @param string|null $phone
     * @param string|null $mobile
     * @param string|null $language
     */
    public function __construct(
        ?string $firstname = null,
        ?string $lastname = null,
        ?string $salutation = null,
        ?string $birthDate = null,
        ?string $company = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $mobile = null,
        ?string $language = null
    ) {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->salutation = $salutation;
        $this->birthDate = $birthDate;
        $this->company = $company;
        $this->email = $email;
        $this->phone = $phone;
        $this->mobile = $mobile;
        $this->language = $language;
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @return string|null
     */
    public function getSalutation(): ?string
    {
        return $this->salutation;
    }

    /**
     * @return string|null
     */
    public function getBirthDate(): ?string
    {
        return $this->birthDate;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param Customer $customer
     *
     * @return Customer
     */
    public function applyTo(Customer $customer): Customer
    {
        $customer->setFirstname($this->firstname);
        $customer->setLastname($this->lastname);
        $customer->setSalutation($this->salutation);
        $customer->setBirthDate($this->birthDate);
        $customer->setCompany($this->company);
        $customer->setEmail($this->email);
        $customer->setPhone($this->phone);
        $customer->setMobile($this->mobile);
        $customer->setLanguage($this->language);

        return $customer;
    }
}
