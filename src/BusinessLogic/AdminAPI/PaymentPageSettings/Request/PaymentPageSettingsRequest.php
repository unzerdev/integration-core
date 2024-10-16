<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request;

use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class PaymentPageRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request
 */
class PaymentPageSettingsRequest
{
    /**
     * @var array $shopName
     *
     * Example [['locale' => 'en', 'value' => 'Shop']]
     */
    private array $shopName;

    /**
     * @var array $shopTagline
     */
    private array $shopTagline;

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
     * @param array $shopName
     * @param array $shopTagline
     * @param string|null $logoImageUrl
     * @param string|null $headerBackgroundColor
     * @param string|null $headerFontColor
     * @param string|null $shopNameBackgroundColor
     * @param string|null $shopNameFontColor
     * @param string|null $shopTaglineBackgroundColor
     * @param string|null $shopTaglineFontColor
     */
    public function __construct(
        array $shopName = [],
        array $shopTagline = [],
        ?string $logoImageUrl = null,
        ?string $headerBackgroundColor = null,
        ?string $headerFontColor = null,
        ?string $shopNameBackgroundColor = null,
        ?string $shopNameFontColor = null,
        ?string $shopTaglineBackgroundColor = null,
        ?string $shopTaglineFontColor = null
    ) {
        $this->shopName = $shopName;
        $this->shopTagline = $shopTagline;
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
     * @throws InvalidTranslatableArrayException
     */

    public function transformToDomainModel(): object
    {
        return new PaymentPageSettings(
            TranslatableLabel::fromArrayToBatch($this->shopName),
            TranslatableLabel::fromArrayToBatch($this->shopTagline),
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