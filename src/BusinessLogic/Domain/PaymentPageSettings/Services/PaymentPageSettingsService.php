<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\V2\Paypage;

/**
 * Class PaymentPageSettingService
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services
 */
class PaymentPageSettingsService
{
    private const AMOUNT = "100.28";
    private const CURRENCY = 'EUR';
    private const CALLBACK_URL = "https://mockurl.com/payment-callback";

    private const LOGO_IMAGE_NAME = 'logo.png';
    private const LOGO_IMAGE_PREVIEW_NAME = 'logo_preview.png';

    private const BACKGROUND_IMAGE_NAME = 'background.png';
    private const BACKGROUND_IMAGE_PREVIEW_NAME = 'background_preview.png';

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
    public function savePaymentPageSettings(PaymentPageSettings $paymentPageSettings): PaymentPageSettings
    {
        if ($paymentPageSettings->getLogoFile()->isFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getLogoFile()->getFileInfo(),
                self::LOGO_IMAGE_NAME
            );
            $paymentPageSettings->getLogoFile()->setUrl($url);
        }

        if ($paymentPageSettings->getBackgroundFile()->isFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getBackgroundFile()->getFileInfo(),
                self::BACKGROUND_IMAGE_NAME
            );
            $paymentPageSettings->getBackgroundFile()->setUrl($url);
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
     *
     * @return Paypage
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function createMockPaypage(PaymentPageSettings $paymentPageSettings): Paypage
    {
        $unzerApi = $this->unzerFactory->makeUnzerAPI();

        if ($paymentPageSettings->getLogoFile()->isFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getLogoFile()->getFileInfo(),
                self::LOGO_IMAGE_PREVIEW_NAME
            );
            $paymentPageSettings->getLogoFile()->setUrl($url);
        }

        if ($paymentPageSettings->getBackgroundFile()->isFileInfo()) {
            $url = $this->uploaderService->uploadImage(
                $paymentPageSettings->getBackgroundFile()->getFileInfo(),
                self::BACKGROUND_IMAGE_PREVIEW_NAME
            );

            $paymentPageSettings->getBackgroundFile()->setUrl($url);
        }

        $payPageRequest = $paymentPageSettings->inflate(
            new Paypage(
                self::AMOUNT,
                self::CURRENCY
            )
        );

        return $unzerApi->createPaypage($payPageRequest);
    }
}
