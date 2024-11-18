<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request;

use SplFileInfo;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidImageUrlException;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\UploadedFile;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;

/**
 * Class PaymentPageRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request
 */
class PaymentPageSettingsRequest
{
    /**
     * @var TranslationCollection
     */
    private TranslationCollection $shopNames;

    /**
     * @var null|string $logoImageUrl
     */
    private ?string $logoImageUrl;

    /**
     * @var SplFileInfo|null
     */
    private ?SplFileInfo $logoFile;

    /**
     * @var null|string $backgroundImageUrl
     */
    private ?string $backgroundImageUrl;

    /**
     * @var SplFileInfo|null
     */
    private ?SplFileInfo $backgroundFile;

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
     * @param TranslationCollection $shopNames
     * @param string|null $logoImageUrl
     * @param SplFileInfo|null $logoFile
     * @param string|null $backgroundImageUrl
     * @param SplFileInfo|null $backgroundFile
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
        TranslationCollection $shopNames,
        ?string $logoImageUrl = null,
        ?SplFileInfo $logoFile = null,
        ?string $backgroundImageUrl = null,
        ?SplFileInfo $backgroundFile = null,
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
        $this->logoImageUrl = $logoImageUrl;
        $this->logoFile = $logoFile;
        $this->backgroundImageUrl = $backgroundImageUrl;
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
     * Transform to Domain model
     *
     * @return PaymentPageSettings
     *
     * @throws InvalidImageUrlException
     */

    public function transformToDomainModel(): object
    {
        return new PaymentPageSettings(
            new UploadedFile($this->logoImageUrl, $this->logoFile),
            new UploadedFile($this->backgroundImageUrl, $this->backgroundFile),
            $this->shopNames,
            $this->headerColor,
            $this->brandColor,
            $this->textColor,
            $this->linkColor,
            $this->backgroundColor,
            $this->footerColor,
            $this->font,
            $this->shadows,
            $this->hideUnzerLogo,
            $this->hideBasket,
            $this->cornerRadius
        );
    }

}
