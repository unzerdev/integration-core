<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request;

use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;

/**
 * Class PaymentPageRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request
 */
class PaymentPageSettingsRequest
{
    /**
     * @var string[] $shopNames
     */
    private array $shopNames;

    /**
     * @var string[] $shopTaglines
     */
    private array $shopTaglines;

    /**
     * @var string $logoImageUrl
     */
    private string $logoImageUrl;

    /**
     * @var string $headerBackgroundColor
     */
    private string $headerBackgroundColor;

    /**
     * @var string $headerFontColor
     */
    private string $headerFontColor;

    /**
     * @var string $shopNameBackgroundColor
     */
    private string $shopNameBackgroundColor;

    /**
     * @var string $shopNameFontColor
     */
    private string $shopNameFontColor;

    /**
     * @var string $shopTaglineBackgroundColor
     */
    private string $shopTaglineBackgroundColor;

    /**
     * @var string $shopTaglineFontColor
     */
    private string $shopTaglineFontColor;

    /**
     * @param array $shopNames
     * @param array $shopTaglines
     * @param string $logoImageUrl
     * @param string $headerBackgroundColor
     * @param string $headerFontColor
     * @param string $shopNameBackgroundColor
     * @param string $shopNameFontColor
     * @param string $shopTaglineBackgroundColor
     * @param string $shopTaglineFontColor
     */
    public function __construct(
        array $shopNames,
        array $shopTaglines,
        string $logoImageUrl,
        string $headerBackgroundColor,
        string $headerFontColor,
        string $shopNameBackgroundColor,
        string $shopNameFontColor,
        string $shopTaglineBackgroundColor,
        string $shopTaglineFontColor
    ) {
        $this->shopNames = $shopNames;
        $this->shopTaglines = $shopTaglines;
        $this->logoImageUrl = $logoImageUrl;
        $this->headerBackgroundColor = $headerBackgroundColor;
        $this->headerFontColor = $headerFontColor;
        $this->shopNameBackgroundColor = $shopNameBackgroundColor;
        $this->shopNameFontColor = $shopNameFontColor;
        $this->shopTaglineBackgroundColor = $shopTaglineBackgroundColor;
        $this->shopTaglineFontColor = $shopTaglineFontColor;
    }

    /**
     * Transform to Domain model
     *
     * @return PaymentPageSettings
     */

    public function transformToDomainModel(): object
    {
        return new PaymentPageSettings(
            $this->shopNames,
            $this->shopTaglines,
            $this->logoImageUrl,
            $this->headerBackgroundColor,
            $this->headerFontColor,
            $this->shopNameBackgroundColor,
            $this->shopNameFontColor,
            $this->shopTaglineBackgroundColor,
            $this->shopTaglineFontColor,
        );
    }

}