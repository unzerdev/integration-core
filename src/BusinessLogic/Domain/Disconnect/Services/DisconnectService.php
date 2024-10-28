<?php

namespace Unzer\Core\BusinessLogic\Domain\Disconnect\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
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
     * @param ConnectionService $connectionService
     * @param PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository
     * @param PaymentPageSettingsRepositoryInterface $paymentPageSettingsRepository
     * @param PaymentStatusMapRepositoryInterface $paymentStatusMapRepository
     * @param TransactionHistoryRepositoryInterface $transactionHistoryRepository
     */
    public function __construct(
        ConnectionService $connectionService,
        PaymentMethodConfigRepositoryInterface $paymentMethodConfigRepository,
        PaymentPageSettingsRepositoryInterface $paymentPageSettingsRepository,
        PaymentStatusMapRepositoryInterface $paymentStatusMapRepository,
        TransactionHistoryRepositoryInterface $transactionHistoryRepository
    ) {
        $this->connectionService = $connectionService;
        $this->paymentMethodConfigRepository = $paymentMethodConfigRepository;
        $this->paymentPageSettingsRepository = $paymentPageSettingsRepository;
        $this->paymentStatusMapRepository = $paymentStatusMapRepository;
        $this->transactionHistoryRepository = $transactionHistoryRepository;
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

        $this->deleteAdditionalSettings();
    }

    /**
     * @return void
     * @throws QueryFilterInvalidParamException
     */

    public function deleteAdditionalSettings(): void
    {
        $this->paymentPageSettingsRepository->deletePaymentPageSettings();
        $this->paymentStatusMapRepository->deletePaymentStatusMapEntity();
        $this->paymentMethodConfigRepository->deletePaymentConfigEntities();
        $this->transactionHistoryRepository->deleteTransactionHistoryEntities();
    }
}
