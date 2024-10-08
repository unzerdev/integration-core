<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models;

/**
 * Class PaymentPageSettings
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models
 */
class PaymentPageSettings
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
     * @return string[]
     */
    public function getShopNames(): array
    {
        return $this->shopNames;
    }

    /**
     * @return string[]
     */
    public function getShopTaglines(): array
    {
        return $this->shopTaglines;
    }

    /**
     * @return string
     */
    public function getLogoImageUrl(): string
    {
        return $this->logoImageUrl;
    }

    /**
     * @return string
     */
    public function getHeaderFontColor(): string
    {
        return $this->headerFontColor;
    }


    /**
     * @return string
     */
    public function getHeaderBackgroundColor(): string
    {
        return $this->headerBackgroundColor;
    }

    /**
     * @return string
     */
    public function getShopNameBackgroundColor(): string
    {
        return $this->shopNameBackgroundColor;
    }

    /**
     * @return string
     */
    public function getShopNameFontColor(): string
    {
        return $this->shopNameFontColor;
    }

    /**
     * @return string
     */
    public function getShopTaglineBackgroundColor(): string
    {
        return $this->shopTaglineBackgroundColor;
    }

    /**
     * @return string
     */
    public function getShopTaglineFontColor(): string
    {
        return $this->shopTaglineFontColor;
    }
}