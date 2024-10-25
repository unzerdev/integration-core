<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces;

use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Interface PaymentStatusMapRepositoryInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces
 */
interface PaymentStatusMapRepositoryInterface
{
    /**
     * @return array
     */
    public function getPaymentStatusMap(): array;

    /**
     * Insert/update PaymentStatusMAp for current store context;
     *
     * @param array $paymentStatusMap
     *
     * @return void
     */
    public function setPaymentStatusMap(array $paymentStatusMap): void;

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deletePaymentStatusMapEntity(): void;
}
