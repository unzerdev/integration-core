<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;

/**
 * Class PaymentMapGetResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentStatusMap\Response
 */
class PaymentMapGetResponse extends Response
{
    /**
     * @var array
     */
    private array $paymentMap;

    /**
     * @param array $paymentMap
     */
    public function __construct(array $paymentMap)
    {
        $this->paymentMap = $paymentMap;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return $this->transformPaymentStatusMap();
    }

    /**
     * @return array
     */
    private function transformPaymentStatusMap(): array
    {
        return [
            'paid' => (string)$this->paymentMap[PaymentStatus::PAID],
            'unpaid' => (string)$this->paymentMap[PaymentStatus::UNPAID],
            'full_refund' => (string)$this->paymentMap[PaymentStatus::FULL_REFUND],
            'cancelled' => (string)$this->paymentMap[PaymentStatus::CANCELLED],
            'chargeback' => (string)$this->paymentMap[PaymentStatus::CHARGEBACK],
            'collection' => (string)$this->paymentMap[PaymentStatus::COLLECTION],
            'partial_refund' => (string)$this->paymentMap[PaymentStatus::PARTIAL_REFUND],
            'declined' => (string)$this->paymentMap[PaymentStatus::DECLINED]
        ];
    }
}
