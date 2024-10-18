<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\PaymentStatusMap;

/**
 * Interface PaymentStatusMapServiceInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\PaymentStatusMap
 */
interface PaymentStatusMapServiceInterface
{
    /**
     * @return array
     */
    public function getDefaultPaymentStatusMap(): array;
}
