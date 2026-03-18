<?php

namespace Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities;

use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidUrlException;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\DomainUrls;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\UploadedFile;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
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
     * @throws InvalidUrlException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];

        $paypageData = $data['paymentPageSettings'] ?? [];

        $this->paymentPageSettings = new DomainPaymentPageSettings(
            new UploadedFile($paypageData['logoImageUrl'] ?? null),
            new UploadedFile($paypageData['backgroundImageUrl'] ?? null),
            new UploadedFile($paypageData['favicon'] ?? null),
            TranslationCollection::fromArray($paypageData['shopNames'] ?? []),
            new DomainUrls(
                $paypageData['helpUrl'] ?? null,
                $paypageData['contactUrl'] ?? null,
                $paypageData['termsAndConditions'] ?? null,
                $paypageData['privacyPolicy'] ?? null,
                $paypageData['imprint'] ?? null,
                $paypageData['subscriptionAgreement'] ?? null
            ),
            $paypageData['headerColor'] ?? null,
            $paypageData['brandColor'] ?? null,
            $paypageData['textColor'] ?? null,
            $paypageData['linkColor'] ?? null,
            $paypageData['backgroundColor'] ?? null,
            $paypageData['footerColor'] ?? null,
            $paypageData['paymentFormBackgroundColor'] ?? null,
            $paypageData['basketBackgroundColor'] ?? null,
            $paypageData['font'] ?? null,
            $paypageData['shadows'] ?? null,
            $paypageData['hideUnzerLogo'] ?? null,
            $paypageData['hideBasket'] ?? null,
            $paypageData['cornerRadius'] ?? null,
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['paymentPageSettings'] = [
            'shopNames' => $this->paymentPageSettings->getShopNames()->toArray(),
            'logoImageUrl' => $this->paymentPageSettings->getLogoFile()->getUrl(),
            'backgroundImageUrl' => $this->paymentPageSettings->getBackgroundFile()->getUrl(),
            'favicon' => $this->paymentPageSettings->getFavicon()->getUrl(),
            'headerColor' => $this->paymentPageSettings->getHeaderColor(),
            'brandColor' => $this->paymentPageSettings->getBrandColor(),
            'textColor' => $this->paymentPageSettings->getTextColor(),
            'linkColor' => $this->paymentPageSettings->getLinkColor(),
            'backgroundColor' => $this->paymentPageSettings->getBackgroundColor(),
            'footerColor' => $this->paymentPageSettings->getFooterColor(),
            'paymentFormBackgroundColor' => $this->paymentPageSettings->getPaymentFormBackgroundColor(),
            'basketBackgroundColor' => $this->paymentPageSettings->getBasketBackgroundColor(),
            'font' => $this->paymentPageSettings->getFont(),
            'shadows' => $this->paymentPageSettings->getShadows(),
            'hideUnzerLogo' => $this->paymentPageSettings->getHideUnzerLogo(),
            'hideBasket' => $this->paymentPageSettings->getHideBasket(),
            'cornerRadius' => $this->paymentPageSettings->getCornerRadius(),
            'helpUrl' => $this->paymentPageSettings->getUrls()->getHelpUrl(),
            'contactUrl' => $this->paymentPageSettings->getUrls()->getContactUrl(),
            'termsAndConditions' => $this->paymentPageSettings->getUrls()->getTermsAndConditions(),
            'privacyPolicy' => $this->paymentPageSettings->getUrls()->getPrivacyPolicy(),
            'imprint' => $this->paymentPageSettings->getUrls()->getImprint(),
            'subscriptionAgreement' => $this->paymentPageSettings->getUrls()->getSubscriptionAgreement(),
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
