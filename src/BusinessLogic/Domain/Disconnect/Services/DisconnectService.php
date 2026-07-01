<?php

namespace Unzer\Core\BusinessLogic\Domain\Disconnect\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectIntegrationServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class DisconnectService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectService
{
    /**
     * @var ConnectionService
     */
    private ConnectionService $connectionService;

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
     * @var DisconnectIntegrationServiceInterface
     */
    private DisconnectIntegrationServiceInterface $disconnectIntegrationService;

    /**
     * @param ConnectionService $connectionService
     * @param PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository
     * @param PaymentPageSettingsRepositoryInterface $paymentPageSettingsRepository
     * @param PaymentStatusMapRepositoryInterface $paymentStatusMapRepository
     * @param TransactionHistoryRepositoryInterface $transactionHistoryRepository
     * @param DisconnectIntegrationServiceInterface $integrationService
     */
    public function __construct(
        ConnectionService $connectionService,
        PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository,
        PaymentPageSettingsRepositoryInterface $paymentPageSettingsRepository,
        PaymentStatusMapRepositoryInterface $paymentStatusMapRepository,
        TransactionHistoryRepositoryInterface $transactionHistoryRepository,
        DisconnectIntegrationServiceInterface $disconnectIntegrationService
    ) {
        $this->connectionService = $connectionService;
        $this->paymentMethodConfigRepository = $paymentMethodConfigRepository;
        $this->paymentPageSettingsRepository = $paymentPageSettingsRepository;
        $this->paymentStatusMapRepository = $paymentStatusMapRepository;
        $this->transactionHistoryRepository = $transactionHistoryRepository;
        $this->disconnectIntegrationService = $disconnectIntegrationService;
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws QueryFilterInvalidParamException
     */
    public function disconnect(): void
    {
        $this->connectionService->deleteWebhooks();

        $this->connectionService->deleteConnectionSettings();

        $this->disconnectIntegrationService->deleteIntegrationData();

        $this->deleteAdditionalSettings();
    }

    /**
     * @return void
     * @throws QueryFilterInvalidParamException
     */

    public function deleteAdditionalSettings(): void
    {
        if ($this->disconnectIntegrationService->shouldDeletePayPageSettings()) {
            $this->paymentPageSettingsRepository->deletePaymentPageSettings();
        }

        $this->paymentStatusMapRepository->deletePaymentStatusMapEntity();
        $this->paymentMethodConfigRepository->deletePaymentConfigEntities();
        $this->transactionHistoryRepository->deleteTransactionHistoryEntities();
    }
}
