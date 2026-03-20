<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\Customer;

use UnzerSDK\Resources\EmbeddedResources\CompanyInfo;
use UnzerSDK\Resources\EmbeddedResources\CompanyOwner;

/**
 * Class CompanyInfoData.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request
 */
class CompanyInfoData
{
    /** @var string|null */
    private ?string $registrationType;

    /** @var string|null */
    private ?string $commercialRegisterNumber;

    /** @var string|null */
    private ?string $function;

    /** @var string|null */
    private ?string $commercialSector;

    /** @var string|null */
    private ?string $companyType;

    /** @var string|null */
    private ?string $ownerFirstname;

    /** @var string|null */
    private ?string $ownerLastname;

    /** @var string|null */
    private ?string $ownerBirthdate;

    /**
     * @param string|null $registrationType
     * @param string|null $commercialRegisterNumber
     * @param string|null $function
     * @param string|null $commercialSector
     * @param string|null $companyType
     * @param string|null $ownerFirstname
     * @param string|null $ownerLastname
     * @param string|null $ownerBirthdate
     */
    public function __construct(
        ?string $registrationType = null,
        ?string $commercialRegisterNumber = null,
        ?string $function = null,
        ?string $commercialSector = null,
        ?string $companyType = null,
        ?string $ownerFirstname = null,
        ?string $ownerLastname = null,
        ?string $ownerBirthdate = null
    ) {
        $this->registrationType = $registrationType;
        $this->commercialRegisterNumber = $commercialRegisterNumber;
        $this->function = $function;
        $this->commercialSector = $commercialSector;
        $this->companyType = $companyType;
        $this->ownerFirstname = $ownerFirstname;
        $this->ownerLastname = $ownerLastname;
        $this->ownerBirthdate = $ownerBirthdate;
    }

    /**
     * @return string|null
     */
    public function getRegistrationType(): ?string
    {
        return $this->registrationType;
    }

    /**
     * @return string|null
     */
    public function getCommercialRegisterNumber(): ?string
    {
        return $this->commercialRegisterNumber;
    }

    /**
     * @return string|null
     */
    public function getFunction(): ?string
    {
        return $this->function;
    }

    /**
     * @return string|null
     */
    public function getCommercialSector(): ?string
    {
        return $this->commercialSector;
    }

    /**
     * @return string|null
     */
    public function getCompanyType(): ?string
    {
        return $this->companyType;
    }

    /**
     * @return string|null
     */
    public function getOwnerFirstname(): ?string
    {
        return $this->ownerFirstname;
    }

    /**
     * @return string|null
     */
    public function getOwnerLastname(): ?string
    {
        return $this->ownerLastname;
    }

    /**
     * @return string|null
     */
    public function getOwnerBirthdate(): ?string
    {
        return $this->ownerBirthdate;
    }

    /**
     * @return CompanyInfo
     */
    public function toUnzerCompanyInfo(): CompanyInfo
    {
        $companyInfo = new CompanyInfo();
        $companyInfo->setRegistrationType($this->registrationType);
        $companyInfo->setCommercialRegisterNumber($this->commercialRegisterNumber);
        $companyInfo->setFunction($this->function);

        if ($this->commercialSector !== null) {
            $companyInfo->setCommercialSector($this->commercialSector);
        }

        $companyInfo->setCompanyType($this->companyType);

        if ($this->ownerFirstname !== null || $this->ownerLastname !== null || $this->ownerBirthdate !== null) {
            $owner = new CompanyOwner();
            $owner->setFirstname($this->ownerFirstname);
            $owner->setLastname($this->ownerLastname);
            $owner->setBirthdate($this->ownerBirthdate);
            $companyInfo->setOwner($owner);
        }

        return $companyInfo;
    }
}
