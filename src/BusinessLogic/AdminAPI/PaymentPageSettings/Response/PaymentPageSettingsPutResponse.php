<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;

/**
 * Class PaymentPageSettingsPutResponse
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response
 */
class PaymentPageSettingsPutResponse extends Response
{
    /** @var PaymentPageSettings $paymentPageSettings */
    private PaymentPageSettings $paymentPageSettings;

    /**
     * @param PaymentPageSettings $paymentPageSettings
     */
    public function __construct(PaymentPageSettings $paymentPageSettings)
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
        return [
            'shopName' => $this->paymentPageSettings->getShopNames()->toArray(),
            'shopTagline' => $this->paymentPageSettings->getShopTaglines()->toArray(),
            'logoImageUrl' => $this->paymentPageSettings->getFile()->getUrl(),
            'headerBackgroundColor' => $this->paymentPageSettings->getHeaderBackgroundColor(),
            'headerFontColor' => $this->paymentPageSettings->getHeaderFontColor(),
            'shopNameBackgroundColor' => $this->paymentPageSettings->getShopNameBackgroundColor(),
            'shopNameFontColor' => $this->paymentPageSettings->getShopNameFontColor(),
            'shopTaglineBackgroundColor' => $this->paymentPageSettings->getShopTaglineBackgroundColor(),
            'shopTaglineFontColor' => $this->paymentPageSettings->getShopTaglineFontColor(),
        ];
    }
}
