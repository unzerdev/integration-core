<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;

/**
 * Class PaymentPageSettingsGetResponse
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response
 */
class PaymentPageSettingsGetResponse extends Response
{
    /**
     * @var PaymentPageSettings|null
     */
    private ?PaymentPageSettings $paymentPageSettings;

    /**
     * @param PaymentPageSettings|null $paymentPageSettings
     */
    public function __construct(?PaymentPageSettings $paymentPageSettings)
    {
        $this->paymentPageSettings = $paymentPageSettings;
    }

    /**
     *  Returns array representation of PaymentPageSettings.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->paymentPageSettings ? $this->transformPaymentPageSettings() :
            [
                'shopNames' => [],
                'shopTaglines' => [],
                'logoImageUrl' => '',
                'headerBackgroundColor' => '',
                'headerFontColor' => '',
                'shopNameBackgroundColor' => '',
                'shopNameFontColor' => '',
                'shopTaglineBackgroundColor' => '',
                'shopTaglineFontColor' => '',
            ];
    }

    /**
     * @return array
     */

    private function transformPaymentPageSettings(): array
    {
        return [
            'shopNames' => $this->paymentPageSettings->getShopNames(),
            'shopTaglines' => $this->paymentPageSettings->getShopTaglines(),
            'logoImageUrl' => $this->paymentPageSettings->getLogoImageUrl(),
            'headerBackgroundColor' => $this->paymentPageSettings->getHeaderBackgroundColor(),
            'headerFontColor' => $this->paymentPageSettings->getHeaderFontColor(),
            'shopNameBackgroundColor' => $this->paymentPageSettings->getShopNameBackgroundColor(),
            'shopNameFontColor' => $this->paymentPageSettings->getShopNameFontColor(),
            'shopTaglineBackgroundColor' => $this->paymentPageSettings->getShopTaglineBackgroundColor(),
            'shopTaglineFontColor' => $this->paymentPageSettings->getShopTaglineFontColor(),
        ];
    }
}