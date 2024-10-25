<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories;

use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;

/**
 * Class PaymentPageSettingsRepository
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories
 */
interface PaymentPageSettingsRepositoryInterface
{
    /**
     * Returns PaymentPageSettings instance for current store context.
     *
     * @return PaymentPageSettings|null
     */
    public function getPaymentPageSettings(): ?PaymentPageSettings;

    /**
     * Insert/update PaymentPageSettings for current store context;
     *
     * @param PaymentPageSettings $paymentPageSettings
     *
     * @return void
     */
    public function setPaymentPageSettings(PaymentPageSettings $paymentPageSettings): void;

    /**
     * Deletes general settings.
     *
     * @return void
     *
     */
    public function deletePaymentPageSettings(): void;
}