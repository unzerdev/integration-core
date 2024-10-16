<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models;

use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

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
     * @var ?string $logoImageUrl
     */
    private ?string $logoImageUrl;

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
    public function getLogoImageUrl(): ?string
    {
        return $this->logoImageUrl;
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
}