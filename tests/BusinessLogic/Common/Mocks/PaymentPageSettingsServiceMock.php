<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services\PaymentPageSettingsService;
use UnzerSDK\Resources\PaymentTypes\Paypage;

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

    private Paypage $paypage;

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

    /**
     * @param PaymentPageSettings $paymentPageSettings
     *
     * @return Paypage
     */
    public function createMockPaypage(PaymentPageSettings $paymentPageSettings): Paypage
    {
        return $this->paypage;
    }

    /**
     * @param Paypage $paypage
     *
     * @return void
     */
    public function setPaypage(Paypage $paypage) : void
    {
        $this->paypage = $paypage;
    }
}