<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Request;

use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;

/**
 * Class SavePaymentMapRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Request
 */
class SavePaymentMapRequest
{
    /**
     * @var array
     */
    private array $paymentStatusMap = [];

    /**
     * @param array $orderStatusMap
     */
    private function __construct(array $orderStatusMap)
    {
        $this->paymentStatusMap = $orderStatusMap;
    }

    /**
     * @param array $payload
     *
     * @return static
     */
    public static function parse(array $payload): self
    {
        return new self(
            [
                PaymentStatus::PAID => $payload['paid'] ?? '',
                PaymentStatus::UNPAID => $payload['unpaid'] ?? '',
                PaymentStatus::FULL_REFUND => $payload['full_refund'] ?? '',
                PaymentStatus::CANCELLED => $payload['cancelled'] ?? '',
                PaymentStatus::CHARGEBACK => $payload['chargeback'] ?? '',
                PaymentStatus::COLLECTION => $payload['collection'] ?? '',
                PaymentStatus::PARTIAL_REFUND => $payload['partial_refund'] ?? '',
                PaymentStatus::DECLINED => $payload['declined'] ?? '',
            ]
        );
    }

    /**
     * @return array
     */
    public function getPaymentStatusMap(): array
    {
        return $this->paymentStatusMap;
    }
}
