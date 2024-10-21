<?php

namespace Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services;

use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Models\PaymentPageSettings;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;

/**
 * Class PaymentPageSettingService
 *
 * @package Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Services
 */
class PaymentPageSettingsService
{
    /**
     * @var PaymentPageSettings
     */
    private $repository;

    /**
     * @var UploaderService
     */
    private UploaderService $uploaderService;

    public function __construct(PaymentPageSettingsRepositoryInterface $repository,
    UploaderService $uploaderService)
    {
        $this->repository = $repository;
        $this->uploaderService = $uploaderService;
    }

    /**
     * @param PaymentPageSettings $paymentPageSettings
     *
     * @return void
     */
    public function savePaymentPageSettings(PaymentPageSettings $paymentPageSettings): void
    {
        if($paymentPageSettings->getFile()->isFileInfo()) {
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
}