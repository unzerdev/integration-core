<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentStatusMap\PaymentStatusMapServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;

/**
 * Class PaymentStatusMapService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Services
 */
class PaymentStatusMapService
{
    /** @var PaymentStatusMapRepositoryInterface $paymentStatusMapRepository */
    private PaymentStatusMapRepositoryInterface $paymentStatusMapRepository;

    /** @var PaymentStatusMapServiceInterface $integrationPaymentStatusMapService */
    private PaymentStatusMapServiceInterface $integrationPaymentStatusMapService;

    /**
     * @param PaymentStatusMapRepositoryInterface $paymentStatusMapRepository
     * @param PaymentStatusMapServiceInterface $integrationPaymentStatusMapService
     */
    public function __construct(
        PaymentStatusMapRepositoryInterface $paymentStatusMapRepository,
        PaymentStatusMapServiceInterface $integrationPaymentStatusMapService
    ) {
        $this->paymentStatusMapRepository = $paymentStatusMapRepository;
        $this->integrationPaymentStatusMapService = $integrationPaymentStatusMapService;
    }

    /**
     * @param array $paymentStatusMap
     *
     * @return void
     */
    public function savePaymentStatusMappingSettings(array $paymentStatusMap): void
    {
        $this->paymentStatusMapRepository->setPaymentStatusMap($paymentStatusMap);
    }

    /**
     * @return array
     */
    public function getPaymentStatusMap(): array
    {
        $paymentStatusMap = $this->paymentStatusMapRepository->getPaymentStatusMap();

        return !empty($paymentStatusMap) ? $paymentStatusMap : $this->getDefaultStatusMapping();
    }

    /**
     * @return array
     */
    private function getDefaultStatusMapping(): array
    {
        return array_merge([
            PaymentStatus::PAID => '',
            PaymentStatus::UNPAID => '',
            PaymentStatus::FULL_REFUND => '',
            PaymentStatus::CANCELLED => '',
            PaymentStatus::CHARGEBACK => '',
            PaymentStatus::COLLECTION => '',
            PaymentStatus::PARTIAL_REFUND => '',
            PaymentStatus::DECLINED => ''
        ], $this->integrationPaymentStatusMapService->getDefaultPaymentStatusMap());
    }
}
