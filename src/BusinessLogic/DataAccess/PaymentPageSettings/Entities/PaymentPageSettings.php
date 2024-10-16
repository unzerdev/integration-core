<?php

namespace Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities;

use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings as DomainPaymentPageSettings;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;

/**
 * Class PaymentPageSettings.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities
 */
class PaymentPageSettings extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var DomainPaymentPageSettings
     */
    protected DomainPaymentPageSettings $paymentPageSettings;

    /**
     * @var string
     */
    protected string $storeId;

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'PaymentPageSettings');
    }

    /**
     * @inheritDoc
     * @throws InvalidTranslatableArrayException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];

        $paypageData = $data['paymentPageSettings'] ?? [];

        $this->paymentPageSettings = new DomainPaymentPageSettings(
            TranslatableLabel::fromArrayToBatch($paypageData['shopName']),
            TranslatableLabel::fromArrayToBatch($paypageData['shopTagline']),
            $paypageData['logoImageUrl'],
            $paypageData['headerBackgroundColor'],
            $paypageData['headerFontColor'],
            $paypageData['shopNameBackgroundColor'],
            $paypageData['shopNameFontColor'],
            $paypageData['shopTaglineBackgroundColor'],
            $paypageData['shopTaglineFontColor'],);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['paymentPageSettings'] = [
            'shopName' => TranslatableLabel::fromBatchToArray($this->paymentPageSettings->getShopName()),
            'shopTagline' => TranslatableLabel::fromBatchToArray($this->paymentPageSettings->getShopTagline()),
            'logoImageUrl' => $this->paymentPageSettings->getLogoImageUrl(),
            'headerBackgroundColor' => $this->paymentPageSettings->getHeaderBackgroundColor(),
            'headerFontColor' => $this->paymentPageSettings->getHeaderFontColor(),
            'shopNameBackgroundColor' => $this->paymentPageSettings->getShopNameBackgroundColor(),
            'shopNameFontColor' => $this->paymentPageSettings->getShopNameFontColor(),
            'shopTaglineBackgroundColor' => $this->paymentPageSettings->getShopTaglineBackgroundColor(),
            'shopTaglineFontColor' => $this->paymentPageSettings->getShopTaglineFontColor(),
        ];

        return $data;
    }

    /**
     * @return DomainPaymentPageSettings
     */
    public function getPaymentPageSettings(): DomainPaymentPageSettings
    {
        return $this->paymentPageSettings;
    }

    /**
     * @param DomainPaymentPageSettings $paymentPageSettings
     *
     * @return void
     */
    public function setPaymentPageSetting(DomainPaymentPageSettings $paymentPageSettings): void
    {
        $this->paymentPageSettings = $paymentPageSettings;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     *
     * @return void
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

}