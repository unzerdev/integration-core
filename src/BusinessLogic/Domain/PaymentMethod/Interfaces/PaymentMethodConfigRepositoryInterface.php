<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces;

use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Interface PaymentMethodConfigRepositoryInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces
 */
interface PaymentMethodConfigRepositoryInterface
{
    /**
     * @return PaymentMethodConfig[]
     */
    public function getPaymentMethodConfigs(): array;

    /**
     * @param PaymentMethodConfig $paymentMethodConfig
     *
     * @return void
     */
    public function savePaymentMethodConfig(PaymentMethodConfig $paymentMethodConfig): void;

    /**
     * @param PaymentMethodConfig $paymentMethodConfig
     *
     * @return void
     */
    public function enablePaymentMethodConfig(PaymentMethodConfig $paymentMethodConfig): void;

    /**
     * @param string $type
     *
     * @return ?PaymentMethodConfig
     */
    public function getPaymentMethodConfigByType(string $type): ?PaymentMethodConfig;

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deletePaymentConfigEntities(): void;

}
