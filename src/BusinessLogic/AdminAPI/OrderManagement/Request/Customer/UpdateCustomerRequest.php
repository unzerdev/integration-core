<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\Customer;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;
use UnzerSDK\Resources\Customer;

/**
 * Class UpdateCustomerRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\Customer
 */
class UpdateCustomerRequest extends Request
{
    /** @var string */
    private string $orderId;

    /** @var string|null */
    private ?string $customerId;

    /** @var CustomerData|null */
    private ?CustomerData $customerData;

    /** @var AddressData|null */
    private ?AddressData $billingAddress;

    /** @var AddressData|null */
    private ?AddressData $shippingAddress;

    /** @var CompanyInfoData|null */
    private ?CompanyInfoData $companyInfo;

    /**
     * @param string $orderId
     * @param string|null $customerId
     * @param CustomerData|null $customerData
     * @param AddressData|null $billingAddress
     * @param AddressData|null $shippingAddress
     * @param CompanyInfoData|null $companyInfo
     */
    public function __construct(
        string $orderId,
        ?string $customerId = null,
        ?CustomerData $customerData = null,
        ?AddressData $billingAddress = null,
        ?AddressData $shippingAddress = null,
        ?CompanyInfoData $companyInfo = null
    ) {
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->customerData = $customerData;
        $this->billingAddress = $billingAddress;
        $this->shippingAddress = $shippingAddress;
        $this->companyInfo = $companyInfo;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * @return CustomerData|null
     */
    public function getCustomerData(): ?CustomerData
    {
        return $this->customerData;
    }

    /**
     * @return AddressData|null
     */
    public function getBillingAddress(): ?AddressData
    {
        return $this->billingAddress;
    }

    /**
     * @return AddressData|null
     */
    public function getShippingAddress(): ?AddressData
    {
        return $this->shippingAddress;
    }

    /**
     * @return CompanyInfoData|null
     */
    public function getCompanyInfo(): ?CompanyInfoData
    {
        return $this->companyInfo;
    }

    /**
     * @return Customer
     */
    public function toUnzerCustomer(): Customer
    {
        $customer = new Customer();

        $customer->setId($this->customerId);
        $this->customerData && $this->customerData->applyTo($customer);
        $this->billingAddress && $customer->setBillingAddress($this->billingAddress->toUnzerAddress());
        $this->shippingAddress && $customer->setShippingAddress($this->shippingAddress->toUnzerAddress());
        $this->companyInfo && $customer->setCompanyInfo($this->companyInfo->toUnzerCompanyInfo());

        return $customer;
    }
}
