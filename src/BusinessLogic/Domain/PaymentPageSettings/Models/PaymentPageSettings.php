<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models;

use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
use UnzerSDK\Resources\V2\Paypage;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Style;

/**
 * Class PaymentPageSettings
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models
 */
class PaymentPageSettings
{

    const EMBEDDED_PAYPAGE_TYPE = "embedded";

    /**
     * @var TranslationCollection $shopNames
     */
    private TranslationCollection $shopNames;

    /**
     * @var null|string $headerColor
     */
    private ?string $headerColor;

    /**
     * @var null|string $brandColor
     */
    private ?string $brandColor;

    /**
     * @var null|string $textColor
     */
    private ?string $textColor;

    /**
     * @var null|string $linkColor
     */
    private ?string $linkColor;

    /**
     * @var null|string $backgroundColor
     */
    private ?string $backgroundColor;

    /**
     * @var null|string $footerColor
     */
    private ?string $footerColor;

    /**
     * @var UploadedFile
     */
    private UploadedFile $logoFile;

    /**
     * @var UploadedFile
     */
    private UploadedFile $backgroundFile;

    /**
     * @var null|string $font
     */
    private ?string $font;

    /**
     * @var null|bool
     */
    private ?bool $shadows = false;

    /**
     * @var null|bool
     */
    private ?bool $hideUnzerLogo = false;

    /**
     * @var null|bool
     */
    private ?bool $hideBasket = false;

    /**
     * @var null|string
     */
    private ?string $cornerRadius;

    /**
     * @var Paypage
     */
    private Paypage $paypage;

    /**
     * @param UploadedFile $logoFile
     * @param UploadedFile $backgroundFile
     * @param TranslationCollection $shopNames
     * @param string|null $headerColor
     * @param string|null $brandColor
     * @param string|null $textColor
     * @param string|null $linkColor
     * @param string|null $backgroundColor
     * @param string|null $footerColor
     * @param string|null $font
     * @param bool|null $shadows
     * @param bool|null $hideUnzerLogo
     * @param bool|null $hideBasket
     * @param string|null $cornerRadius
     */
    public function __construct(
        UploadedFile $logoFile,
        UploadedFile $backgroundFile,
        TranslationCollection $shopNames,
        ?string $headerColor = null,
        ?string $brandColor = null,
        ?string $textColor = null,
        ?string $linkColor = null,
        ?string $backgroundColor = null,
        ?string $footerColor = null,
        ?string $font = null,
        ?bool $shadows = false,
        ?bool $hideUnzerLogo = false,
        ?bool $hideBasket = false,
        ?string $cornerRadius = null
    ) {
        $this->shopNames = $shopNames;
        $this->logoFile = $logoFile;
        $this->backgroundFile = $backgroundFile;
        $this->headerColor = $headerColor;
        $this->brandColor = $brandColor;
        $this->textColor = $textColor;
        $this->linkColor = $linkColor;
        $this->backgroundColor = $backgroundColor;
        $this->footerColor = $footerColor;
        $this->font = $font;
        $this->shadows = $shadows;
        $this->hideUnzerLogo = $hideUnzerLogo;
        $this->hideBasket = $hideBasket;
        $this->cornerRadius = $cornerRadius;
    }

    /**
     * @param Paypage $paypage
     * @param string|null $locale
     * @return Paypage
     */
    public function inflate(Paypage $paypage, ?string $locale = null): Paypage
    {
        $this->paypage = $paypage;

        $this->paypage->setType(self::EMBEDDED_PAYPAGE_TYPE);

        $shopName = $this->shopNames->getTranslationMessage($locale);

        $this->paypage->setShopName($shopName);

        $style = new Style();
        $style
            ->setHeaderColor($this->headerColor)
            ->setBrandColor($this->brandColor)
            ->setTextColor($this->textColor)
            ->setLinkColor($this->linkColor)
            ->setBackgroundColor($this->backgroundColor)
            ->setFooterColor($this->footerColor)
            ->setFont($this->font)
            ->setShadows($this->shadows)
            ->setHideUnzerLogo($this->hideUnzerLogo)
            ->setCornerRadius($this->cornerRadius)
            ->setHideBasket($this->hideBasket)
            ->setLogoImage($this->logoFile->getUrl())
            ->setBackgroundImage($this->backgroundFile->getUrl());

        $this->paypage->setStyle($style);

        return $this->paypage;
    }

    /**
     * @return TranslationCollection
     */
    public function getShopNames(): TranslationCollection
    {
        return $this->shopNames;
    }

    /**
     * @return string|null
     */
    public function getHeaderColor(): ?string
    {
        return $this->headerColor;
    }

    /**
     * @return string|null
     */
    public function getBrandColor(): ?string
    {
        return $this->brandColor;
    }

    /**
     * @return string|null
     */
    public function getLinkColor(): ?string
    {
        return $this->linkColor;
    }

    /**
     * @return string|null
     */
    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    /**
     * @return UploadedFile
     */
    public function getLogoFile(): UploadedFile
    {
        return $this->logoFile;
    }

    /**
     * @return UploadedFile
     */
    public function getBackgroundFile(): UploadedFile
    {
        return $this->backgroundFile;
    }

    /**
     * @return string|null
     */
    public function getFont(): ?string
    {
        return $this->font;
    }

    /**
     * @return bool|null
     */
    public function getShadows(): ?bool
    {
        return $this->shadows;
    }

    /**
     * @return bool|null
     */
    public function getHideUnzerLogo(): ?bool
    {
        return $this->hideUnzerLogo;
    }

    /**
     * @return bool|null
     */
    public function getHideBasket(): ?bool
    {
        return $this->hideBasket;
    }

    /**
     * @return string|null
     */
    public function getCornerRadius(): ?string
    {
        return $this->cornerRadius;
    }

    /**
     * @return Paypage
     */
    public function getPaypage(): Paypage
    {
        return $this->paypage;
    }

    /**
     * @return string|null
     */
    public function getFooterColor(): ?string
    {
        return $this->footerColor;
    }

    /**
     * @return string|null
     */
    public function getTextColor(): ?string
    {
        return $this->textColor;
    }
}
