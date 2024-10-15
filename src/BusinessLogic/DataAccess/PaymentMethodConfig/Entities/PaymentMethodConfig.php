<?php

namespace Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities;

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
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];
        $this->type = $data['type'];

        $paymentMethodConfig = $data['paymentMethodConfig'] ?? [];
        $this->paymentMethodConfig = new DomainPaymentMethodConfig(
            $paymentMethodConfig['type'],
            $paymentMethodConfig['enabled']
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
            'enabled' => $this->paymentMethodConfig->isEnabled()
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
