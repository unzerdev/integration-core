<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Paypage;

/**
 * Class PaymentPageSettingService
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services
 */
class PaymentPageSettingsService
{
    private const AMOUNT = 100.28;
    private const CURRENCY = 'EUR';
    private const CALLBACK_URL = "https://mockurl.com/payment-callback";

    /**
     * @var PaymentPageSettings
     */
    private $repository;

    /**
     * @var UploaderService
     */
    private UploaderService $uploaderService;

    /**
     * @var UnzerFactory
     */
    private UnzerFactory $unzerFactory;


    public function __construct(
        PaymentPageSettingsRepositoryInterface $repository,
        UploaderService $uploaderService,
        UnzerFactory $unzerFactory
    ) {
        $this->repository = $repository;
        $this->uploaderService = $uploaderService;
        $this->unzerFactory = $unzerFactory;
    }

    /**
     * @param PaymentPageSettings $paymentPageSettings
     *
     * @return void
     */
    public function savePaymentPageSettings(PaymentPageSettings $paymentPageSettings): void
    {
        if ($paymentPageSettings->getFile()->isFileInfo()) {
            $url = $this->uploaderService->uploadImage($paymentPageSettings->getFile()->getFileInfo());
            $paymentPageSettings->getFile()->setUrl($url);
        }
        $this->repository->setPaymentPageSettings($paymentPageSettings);
    }

    /**
     * @return PaymentPageSettings|null
     */
    public function getPaymentPageSettings(): ?PaymentPageSettings
    {
        return $this->repository->getPaymentPageSettings();
    }

    /**
     * @param PaymentPageSettings $paymentPageSettings
     *
     * @return Paypage
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function createMockPaypage(PaymentPageSettings $paymentPageSettings): Paypage
    {
        $unzerApi = $this->unzerFactory->makeUnzerAPI();

        $payPageRequest = new Paypage(
            self::AMOUNT,
            self::CURRENCY,
            self::CALLBACK_URL,
        );

        if ($paymentPageSettings->getFile()->isFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getFile()->getFileInfo(),
                "logo_preview.png"
            );
            $paymentPageSettings->getFile()->setUrl($url);
        }

        $shopName = (!empty($paymentPageSettings->getShopName()) && isset($paymentPageSettings->getShopName()[0]))
            ? $paymentPageSettings->getShopName()[0]->getMessage()
            : "";

        $tagline = (!empty($paymentPageSettings->getShopTagline()) && isset($paymentPageSettings->getShopTagline()[0]))
            ? $paymentPageSettings->getShopTagline()[0]->getMessage()
            : "";

        $payPageRequest->setShopName($shopName);
        $payPageRequest->setTagline($tagline);

        if ($paymentPageSettings->getFile()->getUrl()) {
            $payPageRequest->setLogoImage($paymentPageSettings->getFile()->getUrl());
        }

        $css = $this->getCss($paymentPageSettings);

        $filteredCss = array_filter($css);
        if (!empty($filteredCss)) {
            $payPageRequest->setCss($filteredCss);
        }

        return $unzerApi->initPayPageCharge($payPageRequest);
    }

    /**
     * @param PaymentPageSettings $paymentPageSettings
     *
     * @return null[]|string[]
     */
    private function getCss(PaymentPageSettings $paymentPageSettings): array
    {
        return [
            "shopDescription" => $paymentPageSettings->getHeaderFontColor()
                ? "color:" . $paymentPageSettings->getHeaderFontColor()
                : null,
            "header" => $paymentPageSettings->getHeaderBackgroundColor()
                ? "background-color:" . $paymentPageSettings->getHeaderBackgroundColor()
                : null,
            "shopName" => ($paymentPageSettings->getShopNameFontColor()
                && $paymentPageSettings->getShopNameBackgroundColor())
                ? "color:" . $paymentPageSettings->getShopNameFontColor()
                . "; background-color:" . $paymentPageSettings->getShopNameBackgroundColor()
                : null,
            "tagline" => ($paymentPageSettings->getShopTaglineFontColor()
                && $paymentPageSettings->getShopTaglineBackgroundColor())
                ? "color:" . $paymentPageSettings->getShopTaglineFontColor()
                . "; background-color:" . $paymentPageSettings->getShopTaglineBackgroundColor()
                : null,
        ];
    }
}
