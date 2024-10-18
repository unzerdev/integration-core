<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services\PaymentStatusMapService;

/**
 * Class PaymentStatusMapServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class PaymentStatusMapServiceMock extends PaymentStatusMapService
{
    /** @var array  */
    private array $paymentStatusMap = [];

    /**
     * @param array $paymentStatusMap
     *
     * @return void
     */
    public function savePaymentStatusMappingSettings(array $paymentStatusMap): void
    {
        $this->paymentStatusMap = $paymentStatusMap;
    }

    /**
     * @return array
     */
    public function getPaymentStatusMap(): array
    {
        return $this->paymentStatusMap;
    }
}
