<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentStatusMap\PaymentStatusMapServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;

/**
 * Class PaymentStatusMapServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class PaymentStatusMapServiceMock implements PaymentStatusMapServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getDefaultPaymentStatusMap(): array
    {
        return [
            PaymentStatus::PAID => '1',
            PaymentStatus::UNPAID => '2',
            PaymentStatus::FULL_REFUND => '3',
            PaymentStatus::CANCELLED => '4',
            PaymentStatus::CHARGEBACK => '5',
            PaymentStatus::COLLECTION => '6',
            PaymentStatus::PARTIAL_REFUND => '7',
            PaymentStatus::DECLINED => '8'
        ];
    }
}
