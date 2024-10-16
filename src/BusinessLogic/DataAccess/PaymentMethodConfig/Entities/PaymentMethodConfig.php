<?php

namespace Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities;

use Unzer\Core\BusinessLogic\Domain\Country\Exceptions\InvalidCountryArrayException;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidBookingMethodException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;
use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig as DomainPaymentMethodConfig;

/**
 * Class PaymentMethodConfig.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities
 */
class PaymentMethodConfig extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var DomainPaymentMethodConfig
     */
    protected DomainPaymentMethodConfig $paymentMethodConfig;

    /**
     * @var string
     */
    protected string $storeId;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');
        $indexMap->addStringIndex('type');

        return new EntityConfiguration($indexMap, 'PaymentMethodConfig');
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidTranslatableArrayException
     * @throws InvalidCountryArrayException
     * @throws InvalidBookingMethodException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];
        $this->type = $data['type'];

        $paymentMethodConfig = $data['paymentMethodConfig'] ?? [];
        $this->paymentMethodConfig = new DomainPaymentMethodConfig(
            $paymentMethodConfig['type'],
            $paymentMethodConfig['enabled'],
            TranslatableLabel::fromArrayToBatch($paymentMethodConfig['name']),
            TranslatableLabel::fromArrayToBatch($paymentMethodConfig['description']),
            $paymentMethodConfig['bookingMethod'] ? BookingMethod::parse($paymentMethodConfig['bookingMethod']) : null,
            $paymentMethodConfig['statusIdToCharge'],
            $paymentMethodConfig['minOrderAmount'],
            $paymentMethodConfig['maxOrderAmount'],
            $paymentMethodConfig['surcharge'],
            Country::fromArrayToBatch($paymentMethodConfig['restrictedCountries']),
            $paymentMethodConfig['sendBasketData'],
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['type'] = $this->type;
        $data['paymentMethodConfig'] = [
            'type' => $this->paymentMethodConfig->getType(),
            'enabled' => $this->paymentMethodConfig->isEnabled(),
            'name' => TranslatableLabel::fromBatchToArray($this->paymentMethodConfig->getName()),
            'bookingMethod' => $this->paymentMethodConfig->getBookingMethod() ?
                $this->paymentMethodConfig->getBookingMethod()->getBookingMethod() : null,
            'description' => TranslatableLabel::fromBatchToArray($this->paymentMethodConfig->getDescription()),
            'statusIdToCharge' => $this->paymentMethodConfig->getStatusIdToCharge(),
            'minOrderAmount' => $this->paymentMethodConfig->getMinOrderAmount(),
            'maxOrderAmount' => $this->paymentMethodConfig->getMaxOrderAmount(),
            'surcharge' => $this->paymentMethodConfig->getSurcharge(),
            'restrictedCountries' => Country::fromBatchToArray($this->paymentMethodConfig->getRestrictedCountries()),
            'sendBasketData' => $this->paymentMethodConfig->isSendBasketData()
        ];

        return $data;
    }

    /**
     * @return DomainPaymentMethodConfig
     */
    public function getPaymentMethodConfig(): DomainPaymentMethodConfig
    {
        return $this->paymentMethodConfig;
    }

    /**
     * @param DomainPaymentMethodConfig $paymentMethodConfig
     *
     * @return void
     */
    public function setPaymentMethodConfig(DomainPaymentMethodConfig $paymentMethodConfig): void
    {
        $this->paymentMethodConfig = $paymentMethodConfig;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     *
     * @return void
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
