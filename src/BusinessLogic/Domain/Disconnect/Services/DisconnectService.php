<?php

namespace Unzer\Core\BusinessLogic\Domain\Disconnect\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class DisconnectService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectService
{
    /**
     * @var UnzerFactory
     */
    private UnzerFactory $unzerFactory;

    /**
     * @var ConnectionSettingsRepositoryInterface
     */
    private ConnectionSettingsRepositoryInterface $connectionSettingsRepository;

    /**
     * @var WebhookDataRepositoryInterface
     */
    private WebhookDataRepositoryInterface $webhookDataRepository;

    /**
     * @var PaymentMethodConfigRepositoryInterface
     */
    private PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository;

    /**
     * @var PaymentPageSettingsRepositoryInterface
     */
    private PaymentPageSettingsRepositoryInterface $paymentPageSettingsRepository;

    /**
     * @var PaymentStatusMapRepositoryInterface
     */
    private PaymentStatusMapRepositoryInterface $paymentStatusMapRepository;

    /**
     * @var TransactionHistoryRepositoryInterface
     */
    private TransactionHistoryRepositoryInterface $transactionHistoryRepository;

    /**
     * @param UnzerFactory $unzerFactory
     * @param ConnectionSettingsRepositoryInterface $connectionSettingsRepository
     * @param WebhookDataRepositoryInterface $webhookDataRepository
     * @param PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository
     * @param PaymentPageSettingsRepositoryInterface $paymentPageSettingsRepository
     * @param PaymentStatusMapRepositoryInterface $paymentStatusMapRepository
     * @param TransactionHistoryRepositoryInterface $transactionHistoryRepository
     */
    public function __construct(
        UnzerFactory $unzerFactory,
        ConnectionSettingsRepositoryInterface $connectionSettingsRepository,
        WebhookDataRepositoryInterface $webhookDataRepository,
        PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository,
        PaymentPageSettingsRepositoryInterface $paymentPageSettingsRepository,
        PaymentStatusMapRepositoryInterface $paymentStatusMapRepository,
        TransactionHistoryRepositoryInterface $transactionHistoryRepository
    ) {
        $this->unzerFactory = $unzerFactory;
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->webhookDataRepository = $webhookDataRepository;
        $this->paymentMethodConfigRepository = $paymentMethodConfigRepository;
        $this->paymentPageSettingsRepository = $paymentPageSettingsRepository;
        $this->paymentStatusMapRepository = $paymentStatusMapRepository;
        $this->transactionHistoryRepository = $transactionHistoryRepository;
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws QueryFilterInvalidParamException
     */
    public function disconnect(): void
    {
        $this->unzerFactory->makeUnzerAPI()->deleteAllWebhooks();
        $this->webhookDataRepository->deleteWebhookData();
        $this->connectionSettingsRepository->deleteConnectionSettings();
        $this->paymentPageSettingsRepository->deletePaymentPageSettings();
        $this->paymentStatusMapRepository->deletePaymentStatusMapEntity();
        $this->paymentMethodConfigRepository->deletePaymentConfigEntities();
        $this->transactionHistoryRepository->deleteTransactionHistoryEntities();
    }
}
