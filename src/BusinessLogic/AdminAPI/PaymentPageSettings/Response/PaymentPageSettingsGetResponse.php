<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;

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
        return $this->transformPaymentPageSettings();

    }

    /**
     * @return array
     */
    private function transformPaymentPageSettings(): array
    {
        if($this->paymentPageSettings === null) {
            return [
                'shopName' => [],
                'logoImageUrl' => null,
                'backgroundImageUrl' => null,
                'headerColor' => null,
                'brandColor' => null,
                'textColor' => null,
                'linkColor' => null,
                'backgroundColor' => null,
                'footerColor' => null,
                'font' => null,
                'shadows' => null,
                'hideUnzerLogo' => null,
                'hideBasket' => null,
                'cornerRadius' => null,
            ];
        }

        return [
            'shopName' => TranslationCollection::translationsToArray($this->paymentPageSettings->getShopNames()),
            'logoImageUrl' => $this->paymentPageSettings->getLogoFile()->getUrl(),
            'backgroundImageUrl' => $this->paymentPageSettings->getBackgroundFile()->getUrl(),
            'headerColor' => $this->paymentPageSettings->getHeaderColor(),
            'brandColor' => $this->paymentPageSettings->getBrandColor(),
            'textColor' => $this->paymentPageSettings->getTextColor(),
            'linkColor' => $this->paymentPageSettings->getLinkColor(),
            'backgroundColor' => $this->paymentPageSettings->getBackgroundColor(),
            'footerColor' => $this->paymentPageSettings->getFooterColor(),
            'font' => $this->paymentPageSettings->getFont(),
            'shadows' => $this->paymentPageSettings->getShadows(),
            'hideUnzerLogo' => $this->paymentPageSettings->getHideUnzerLogo(),
            'hideBasket' => $this->paymentPageSettings->getHideBasket(),
            'cornerRadius' => $this->paymentPageSettings->getCornerRadius()
        ];
    }
}
