<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidUrlException;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Class PaymentPageSettingService
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services
 */
class PaymentPageSettingsService
{
    protected const AMOUNT = "100.28";
    protected const CURRENCY = 'EUR';

    protected const LOGO_IMAGE_NAME = 'logo.png';
    protected const LOGO_IMAGE_PREVIEW_NAME = 'logo_preview.png';
    protected const BACKGROUND_IMAGE_NAME = 'background.png';
    protected const BACKGROUND_IMAGE_PREVIEW_NAME = 'background_preview.png';
    protected const FAVICON_IMAGE_NAME = 'favicon.png';
    protected const FAVICON_IMAGE_PREVIEW_NAME = 'favicon_preview.png';

    /**
     * @var PaymentPageSettings
     */
    protected $repository;

    /**
     * @var UploaderService
     */
    protected UploaderService $uploaderService;

    /**
     * @var UnzerFactory
     */
    protected UnzerFactory $unzerFactory;

    /**
     * @param PaymentPageSettingsRepositoryInterface $repository
     * @param UploaderService $uploaderService
     * @param UnzerFactory $unzerFactory
     */
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
     * @return PaymentPageSettings
     *
     * @throws InvalidUrlException
     */
    public function savePaymentPageSettings(PaymentPageSettings $paymentPageSettings): PaymentPageSettings
    {
        if (!$paymentPageSettings->getLogoFile()->hasFileInfo()) {
            $this->validateImageUrl($paymentPageSettings->getLogoFile()->getUrl());
        }

        if (!$paymentPageSettings->getBackgroundFile()->hasFileInfo()) {
            $this->validateImageUrl($paymentPageSettings->getBackgroundFile()->getUrl());
        }

        if (!$paymentPageSettings->getFavicon()->hasFileInfo()) {
            $this->validateImageUrl($paymentPageSettings->getFavicon()->getUrl());
        }

        if ($paymentPageSettings->getLogoFile()->hasFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getLogoFile()->getFileInfo(),
                self::LOGO_IMAGE_NAME
            );
            $paymentPageSettings->getLogoFile()->setUrl($url);
        }

        if ($paymentPageSettings->getBackgroundFile()->hasFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getBackgroundFile()->getFileInfo(),
                self::BACKGROUND_IMAGE_NAME
            );
            $paymentPageSettings->getBackgroundFile()->setUrl($url);
        }

        if ($paymentPageSettings->getFavicon()->hasFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getFavicon()->getFileInfo(),
                self::FAVICON_IMAGE_NAME
            );
            $paymentPageSettings->getFavicon()->setUrl($url);
        }

        $this->repository->setPaymentPageSettings($paymentPageSettings);

        return $paymentPageSettings;
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
     * @param string $paypageType
     *
     * @return Paypage
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function createMockPaypage(PaymentPageSettings $paymentPageSettings, string $paypageType): Paypage
    {
        $unzerApi = $this->unzerFactory->makeUnzerAPI();

        if ($paymentPageSettings->getLogoFile()->hasFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getLogoFile()->getFileInfo(),
                self::LOGO_IMAGE_PREVIEW_NAME
            );
            $paymentPageSettings->getLogoFile()->setUrl($url);
        }

        if ($paymentPageSettings->getBackgroundFile()->hasFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getBackgroundFile()->getFileInfo(),
                self::BACKGROUND_IMAGE_PREVIEW_NAME
            );

            $paymentPageSettings->getBackgroundFile()->setUrl($url);
        }

        if ($paymentPageSettings->getFavicon()->hasFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getFavicon()->getFileInfo(),
                self::FAVICON_IMAGE_PREVIEW_NAME
            );

            $paymentPageSettings->getFavicon()->setUrl($url);
        }

        $payPageRequest = $paymentPageSettings->inflate(
            new Paypage(
                self::AMOUNT,
                self::CURRENCY
            )
        );

        $basket = $this->createMockBasket();
        $basket = $unzerApi->createBasket($basket);

        $payPageRequest->setResources(new Resources(null, $basket->getId()));

        $payPageRequest->setType($paypageType);

        return $unzerApi->createPaypage($payPageRequest);
    }

    /**
     * @param ?string $url
     *
     * @return void
     *
     * @throws InvalidUrlException
     */
    protected function validateImageUrl(?string $url): void
    {
        if (!$url) {
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException(
                new TranslatableLabel('Url is not valid.', 'designPage.invalidUrl')
            );
        }
    }

    /**
     * @return Basket
     */
    private function createMockBasket(): Basket
    {
        $basket = new Basket('1', self::AMOUNT, self::CURRENCY);
        $basket->setTotalValueGross(self::AMOUNT);

        $basket->addBasketItem($this->createMockBasketItem('Item 1', 'testItem1', self::AMOUNT - 40, 0));
        $basket->addBasketItem($this->createMockBasketItem('Item 2', 'testItem2', 40 , 0));

        return $basket;
    }

    /**
     * @param string $title
     * @param string $reference
     * @param float $amount
     * @param float $discount
     *
     * @return BasketItem
     */
    private function createMockBasketItem(string $title, string $reference, float $amount, float $discount): BasketItem
    {
        $basketItem = new BasketItem($title);

        $basketItem->setBasketItemReferenceId($reference);
        $basketItem->setAmountDiscountPerUnitGross($discount);
        $basketItem->setAmountPerUnitGross($amount);

        return $basketItem;
    }
}
