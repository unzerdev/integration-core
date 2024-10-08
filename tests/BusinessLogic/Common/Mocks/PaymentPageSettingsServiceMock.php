<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;

/**
 * Class PaymentPageSettingsServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class PaymentPageSettingsServiceMock extends PaymentPageSettingsService
{
    /**
     * @var PaymentPageSettings|null
     */
    private ?PaymentPageSettings $paymentPageSettings = null;

    /**
     * @param PaymentPageSettings $paymentPageSettings
     *
     * @return void
     */
    public function savePaymentPageSettings(PaymentPageSettings $paymentPageSettings): void
    {
    }

    /**
     *
     * @return PaymentPageSettings|null
     */
    public function getPaymentPageSettings(): ?PaymentPageSettings
    {
        return $this->paymentPageSettings;
    }

    /**
     * @param PaymentPageSettings $paymentPageSettings
     *
     * @return void
     */
    public function setPaymentPageSettings(PaymentPageSettings $paymentPageSettings): void
    {
        $this->paymentPageSettings = $paymentPageSettings;
    }

    /**
     *
     * @return void
     */
    public function deletePaymentPageSettings(): void
    {
        $this->paymentPageSettings = null;
    }
}