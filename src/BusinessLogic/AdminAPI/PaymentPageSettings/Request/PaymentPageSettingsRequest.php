<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request;

use SplFileInfo;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidUrlException;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\DomainUrls;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\UploadedFile;
use Unzer\Core\BusinessLogic\Domain\Payments\PaymentPage\Enums\PaymentPageType;
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
     * @var null|string $logoImageUrl
     */
    private ?string $faviconImageUrl;

    /**
     * @var SplFileInfo|null
     */
    private ?SplFileInfo $faviconFile;

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
     * @var null|string $paymentFormBackgroundColor
     */
    private ?string $paymentFormBackgroundColor;

    /**
     * @var null|string $basketBackgroundColor
     */
    private ?string $basketBackgroundColor;

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
     * @var null|string
     */
    private ?string $helpUrl = null;

    /**
     * @var null|string
     */
    private ?string $contactUrl = null;

    /**
     * @var null|string
     */
    private ?string $termsAndConditions = null;

    /**
     * @var null|string
     */
    private ?string $privacyPolicy = null;

    /**
     * @var null|string
     */
    private ?string $imprint = null;

    /**
     * @var null|string
     */
    private ?string $subscriptionAgreement = null;

    private string $paypageType;

    /**
     * @param TranslationCollection $shopNames
     * @param string|null $logoImageUrl
     * @param SplFileInfo|null $logoFile
     * @param string|null $backgroundImageUrl
     * @param SplFileInfo|null $backgroundFile
     * @param string|null $faviconImageUrl
     * @param SplFileInfo|null $faviconFile
     * @param string|null $headerColor
     * @param string|null $brandColor
     * @param string|null $textColor
     * @param string|null $linkColor
     * @param string|null $backgroundColor
     * @param string|null $footerColor
     * @param string|null $paymentFormBackgroundColor
     * @param string|null $basketBackgroundColor
     * @param string|null $font
     * @param bool|null $shadows
     * @param bool|null $hideUnzerLogo
     * @param bool|null $hideBasket
     * @param string|null $cornerRadius
     * @param string|null $helpUrl
     * @param string|null $contactUrl
     * @param string|null $termsAndConditions
     * @param string|null $privacyPolicy
     * @param string|null $imprint
     * @param string|null $subscriptionAgreement
     * @param string|null $paypageType
     */
    public function __construct(
        TranslationCollection $shopNames,
        ?string $logoImageUrl = null,
        ?SplFileInfo $logoFile = null,
        ?string $backgroundImageUrl = null,
        ?SplFileInfo $backgroundFile = null,
        ?string $faviconImageUrl = null,
        ?SplFileInfo $faviconFile = null,
        ?string $headerColor = null,
        ?string $brandColor = null,
        ?string $textColor = null,
        ?string $linkColor = null,
        ?string $backgroundColor = null,
        ?string $footerColor = null,
        ?string $paymentFormBackgroundColor = null,
        ?string $basketBackgroundColor = null,
        ?string $font = null,
        ?bool $shadows = false,
        ?bool $hideUnzerLogo = false,
        ?bool $hideBasket = false,
        ?string $cornerRadius = null,
        ?string $helpUrl = null,
        ?string $contactUrl = null,
        ?string $termsAndConditions = null,
        ?string $privacyPolicy = null,
        ?string $imprint = null,
        ?string $subscriptionAgreement = null,
        ?string $paypageType = PaymentPageType::EMBEDDED
    ) {
        $this->shopNames = $shopNames;
        $this->logoImageUrl = $logoImageUrl;
        $this->logoFile = $logoFile;
        $this->backgroundImageUrl = $backgroundImageUrl;
        $this->backgroundFile = $backgroundFile;
        $this->faviconImageUrl = $faviconImageUrl;
        $this->faviconFile = $faviconFile;
        $this->headerColor = $headerColor;
        $this->brandColor = $brandColor;
        $this->textColor = $textColor;
        $this->linkColor = $linkColor;
        $this->backgroundColor = $backgroundColor;
        $this->footerColor = $footerColor;
        $this->paymentFormBackgroundColor = $paymentFormBackgroundColor;
        $this->basketBackgroundColor = $basketBackgroundColor;
        $this->font = $font;
        $this->shadows = $shadows;
        $this->hideUnzerLogo = $hideUnzerLogo;
        $this->hideBasket = $hideBasket;
        $this->cornerRadius = $cornerRadius;
        $this->helpUrl = $helpUrl;
        $this->contactUrl = $contactUrl;
        $this->termsAndConditions = $termsAndConditions;
        $this->privacyPolicy = $privacyPolicy;
        $this->imprint = $imprint;
        $this->subscriptionAgreement = $subscriptionAgreement;
        $this->paypageType = $paypageType;
    }

    /**
     * Transform to Domain model
     *
     * @return PaymentPageSettings
     *
     * @throws InvalidUrlException
     */

    public function transformToDomainModel(): object
    {
        return new PaymentPageSettings(
            new UploadedFile($this->logoImageUrl, $this->logoFile),
            new UploadedFile($this->backgroundImageUrl, $this->backgroundFile),
            new UploadedFile($this->faviconImageUrl, $this->faviconFile),
            $this->shopNames,
            new DomainUrls($this->helpUrl, $this->contactUrl, $this->termsAndConditions, $this->privacyPolicy,
                $this->imprint, $this->subscriptionAgreement),
            $this->headerColor,
            $this->brandColor,
            $this->textColor,
            $this->linkColor,
            $this->backgroundColor,
            $this->footerColor,
            $this->paymentFormBackgroundColor,
            $this->basketBackgroundColor,
            $this->font,
            $this->shadows,
            $this->hideUnzerLogo,
            $this->hideBasket,
            $this->cornerRadius,
        );
    }

    /**
     * @return string
     */
    public function getPaypageType(): string
    {
        return $this->paypageType;
    }
}
