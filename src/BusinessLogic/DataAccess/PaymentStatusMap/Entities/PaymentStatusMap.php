<?php

namespace Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Entities;

use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;

/**
 * Class PaymentStatusMap.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Entities
 */
class PaymentStatusMap extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected string $storeId;

    /**
     * @var array
     */
    protected array $paymentStatusMap;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];
        $this->paymentStatusMap = $data['paymentStatusMap'] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['paymentStatusMap'] = $this->paymentStatusMap;

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'PaymentStatusMap');
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
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return array
     */
    public function getPaymentStatusMap(): array
    {
        return $this->paymentStatusMap;
    }

    /**
     * @param array $paymentStatusMap
     */
    public function setPaymentStatusMap(array $paymentStatusMap): void
    {
        $this->paymentStatusMap = $paymentStatusMap;
    }
}
