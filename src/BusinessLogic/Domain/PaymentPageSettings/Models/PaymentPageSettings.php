<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models;

use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use UnzerSDK\Resources\PaymentTypes\Paypage;

/**
 * Class PaymentPageSettings
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models
 */
class PaymentPageSettings
{
    /**
     * @var TranslatableLabel[] $shopName
     */
    private array $shopName = [];

    /**
     * @var TranslatableLabel[] $shopTagline
     */
    private array $shopTagline = [];

    /**
     * @var ?string $headerBackgroundColor
     */
    private ?string $headerBackgroundColor;

    /**
     * @var ?string $headerFontColor
     */
    private ?string $headerFontColor;

    /**
     * @var ?string $shopNameBackgroundColor
     */
    private ?string $shopNameBackgroundColor;

    /**
     * @var ?string $shopNameFontColor
     */
    private ?string $shopNameFontColor;

    /**
     * @var ?string $shopTaglineBackgroundColor
     */
    private ?string $shopTaglineBackgroundColor;

    /**
     * @var ?string $shopTaglineFontColor
     */
    private ?string $shopTaglineFontColor;

    /**
     * @var UploadedFile
     */
    private UploadedFile $file;

    /**
     * @var Paypage
     */
    private Paypage $paypage;

    /**
     * @var string
     */
    private string $defaultLocale = 'default';

    /**
     * @param array $shopName
     * @param array $shopTagline
     * @param UploadedFile $file
     * @param string|null $headerBackgroundColor
     * @param string|null $headerFontColor
     * @param string|null $shopNameBackgroundColor
     * @param string|null $shopNameFontColor
     * @param string|null $shopTaglineBackgroundColor
     * @param string|null $shopTaglineFontColor
     */
    public function __construct(
        UploadedFile $file,
        array $shopName = [],
        array $shopTagline = [],
        ?string $headerBackgroundColor = null,
        ?string $headerFontColor = null,
        ?string $shopNameBackgroundColor = null,
        ?string $shopNameFontColor = null,
        ?string $shopTaglineBackgroundColor = null,
        ?string $shopTaglineFontColor = null
    ) {
        $this->shopName = $shopName;
        $this->shopTagline = $shopTagline;
        $this->file = $file;
        $this->headerBackgroundColor = $headerBackgroundColor;
        $this->headerFontColor = $headerFontColor;
        $this->shopNameBackgroundColor = $shopNameBackgroundColor;
        $this->shopNameFontColor = $shopNameFontColor;
        $this->shopTaglineBackgroundColor = $shopTaglineBackgroundColor;
        $this->shopTaglineFontColor = $shopTaglineFontColor;
    }

    /**
     * @param Paypage $paypage
     *
     * @return Paypage
     */
    public function inflate(Paypage $paypage): Paypage
    {
        $this->paypage = $paypage;

        $shopName = $this->getShopNameByLocale() ? $this->getShopNameByLocale($this->defaultLocale)->getMessage() : '';
        $tagline = $this->getTaglineByLocale() ? $this->getTaglineByLocale($this->defaultLocale)->getMessage() : '';

        $this->paypage->setShopName($shopName);
        $this->paypage->setTagline($tagline);

        if ($this->getFile()->getUrl()) {
            $this->paypage->setLogoImage($this->getFile()->getUrl());
        }

        $css = $this->getCss();

        if (!empty($css)) {
            $this->paypage->setCss($css);
        }

        return $this->paypage;
    }

    /**
     * @return UploadedFile
     */
    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    /**
     * @return TranslatableLabel[]
     */
    public function getShopName(): array
    {
        return $this->shopName;
    }

    /**
     * @return TranslatableLabel[]
     */
    public function getShopTagline(): array
    {
        return $this->shopTagline;
    }

    /**
     * @return ?string
     */
    public function getHeaderBackgroundColor(): ?string
    {
        return $this->headerBackgroundColor;
    }

    /**
     * @return ?string
     */
    public function getHeaderFontColor(): ?string
    {
        return $this->headerFontColor;
    }

    /**
     * @return ?string
     */
    public function getShopNameBackgroundColor(): ?string
    {
        return $this->shopNameBackgroundColor;
    }

    /**
     * @return ?string
     */
    public function getShopNameFontColor(): ?string
    {
        return $this->shopNameFontColor;
    }

    /**
     * @return ?string
     */
    public function getShopTaglineFontColor(): ?string
    {
        return $this->shopTaglineFontColor;
    }

    /**
     * @return ?string
     */
    public function getShopTaglineBackgroundColor(): ?string
    {
        return $this->shopTaglineBackgroundColor;
    }

    /**
     * @param string $locale
     *
     * @return TranslatableLabel|null
     */
    public function getShopNameByLocale(string $locale = 'default'): ?TranslatableLabel
    {
        foreach ($this->shopName as $label) {
            if ($label->getCode() === $locale) {
                return $label;
            }
        }

        return null;
    }

    /**
     * @param string $locale
     *
     * @return TranslatableLabel|null
     */
    public function getTaglineByLocale(string $locale = 'default'): ?TranslatableLabel
    {
        foreach ($this->shopTagline as $label) {
            if ($label->getCode() === $locale) {
                return $label;
            }
        }

        return null;
    }

    /**
     * @param string $locale
     *
     * @return void
     */
    public function setDefaultLocale(string $locale): void
    {
        $this->defaultLocale = $locale;
    }

    /**
     * @return array
     */
    private function getCss(): array
    {
        return array_filter([
            "shopDescription" => $this->getHeaderFontColor()
                ? "color:" . $this->getHeaderFontColor()
                : null,
            "header" => $this->getHeaderBackgroundColor()
                ? "background-color:" . $this->getHeaderBackgroundColor()
                : null,
            "shopName" => ($this->getShopNameFontColor()
                && $this->getShopNameBackgroundColor())
                ? "color:" . $this->getShopNameFontColor()
                . "; background-color:" . $this->getShopNameBackgroundColor()
                : null,
            "tagline" => ($this->getShopTaglineFontColor()
                && $this->getShopTaglineBackgroundColor())
                ? "color:" . $this->getShopTaglineFontColor()
                . "; background-color:" . $this->getShopTaglineBackgroundColor()
                : null,
        ]);
    }
}
