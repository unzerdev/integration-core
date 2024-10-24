<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

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
                'shopName' => [],
                'shopTagline' => [],
                'logoImageUrl' => null,
                'headerBackgroundColor' => null,
                'headerFontColor' => null,
                'shopNameBackgroundColor' => null,
                'shopNameFontColor' => null,
                'shopTaglineBackgroundColor' => null,
                'shopTaglineFontColor' => null,
            ];
    }

    /**
     * @return array
     */

    private function transformPaymentPageSettings(): array
    {
        return [
            'shopName' => $this->translatableLabelsToArray($this->paymentPageSettings->getShopName()),
            'shopTagline' => $this->translatableLabelsToArray($this->paymentPageSettings->getShopTagline()),
            'logoImageUrl' => $this->paymentPageSettings->getFile()->getUrl(),
            'headerBackgroundColor' => $this->paymentPageSettings->getHeaderBackgroundColor(),
            'headerFontColor' => $this->paymentPageSettings->getHeaderFontColor(),
            'shopNameBackgroundColor' => $this->paymentPageSettings->getShopNameBackgroundColor(),
            'shopNameFontColor' => $this->paymentPageSettings->getShopNameFontColor(),
            'shopTaglineBackgroundColor' => $this->paymentPageSettings->getShopTaglineBackgroundColor(),
            'shopTaglineFontColor' => $this->paymentPageSettings->getShopTaglineFontColor(),
        ];
    }

    /**
     * @param TranslatableLabel[] $labels
     *
     * @return array
     */
    private function translatableLabelsToArray(array $labels): array
    {
        return array_map(function ($label) {
            return [
                'locale' => $label->getCode(),
                'value' => $label->getMessage()
            ];
        }, $labels);
    }
}