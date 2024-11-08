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
     * @var TranslationCollection $shopTaglines
     */
    private TranslationCollection $shopTaglines;

    /**
     * @var null|string $logoImageUrl
     */
    private ?string $logoImageUrl;

    /**
     * @var null|string $headerBackgroundColor
     */
    private ?string $headerBackgroundColor;

    /**
     * @var null|string $headerFontColor
     */
    private ?string $headerFontColor;

    /**
     * @var null|string $shopNameBackgroundColor
     */
    private ?string $shopNameBackgroundColor;

    /**
     * @var null|string $shopNameFontColor
     */
    private ?string $shopNameFontColor;

    /**
     * @var null|string $shopTaglineBackgroundColor
     */
    private ?string $shopTaglineBackgroundColor;

    /**
     * @var null|string $shopTaglineFontColor
     */
    private ?string $shopTaglineFontColor;

    /**
     * @var SplFileInfo|null
     */
    private ?SplFileInfo $file;

    /**
     * @param TranslationCollection $shopNames
     * @param TranslationCollection $shopTaglines
     * @param string|null $logoImageUrl
     * @param SplFileInfo|null $file
     * @param string|null $headerBackgroundColor
     * @param string|null $headerFontColor
     * @param string|null $shopNameBackgroundColor
     * @param string|null $shopNameFontColor
     * @param string|null $shopTaglineBackgroundColor
     * @param string|null $shopTaglineFontColor
     */
    public function __construct(
        TranslationCollection $shopNames,
        TranslationCollection $shopTaglines,
        ?string $logoImageUrl = null,
        ?SplFileInfo $file = null,
        ?string $headerBackgroundColor = null,
        ?string $headerFontColor = null,
        ?string $shopNameBackgroundColor = null,
        ?string $shopNameFontColor = null,
        ?string $shopTaglineBackgroundColor = null,
        ?string $shopTaglineFontColor = null
    ) {
        $this->shopNames = $shopNames;
        $this->shopTaglines = $shopTaglines;
        $this->logoImageUrl = $logoImageUrl;
        $this->file = $file;
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
     *
     * @throws InvalidImageUrlException
     */

    public function transformToDomainModel(): object
    {
        return new PaymentPageSettings(
            new UploadedFile($this->logoImageUrl, $this->file),
            $this->shopNames,
            $this->shopTaglines,
            $this->headerBackgroundColor,
            $this->headerFontColor,
            $this->shopNameBackgroundColor,
            $this->shopNameFontColor,
            $this->shopTaglineBackgroundColor,
            $this->shopTaglineFontColor,
        );
    }

}
