<?php

namespace Unzer\Core\BusinessLogic\Domain\Disconnect\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Unzer;

/**
 * Class DisconnectService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectService
{
    /**
     * @var Unzer
     */
    private Unzer $unzer;

    /**
     * @var ConnectionSettingsRepositoryInterface
     */
    private ConnectionSettingsRepositoryInterface $connectionSettingsRepository;

    /**
     * @var WebhookDataRepositoryInterface
     */
    private WebhookDataRepositoryInterface $webhookDataRepository;

    /**
     * @param Unzer $unzer
     * @param ConnectionSettingsRepositoryInterface $connectionSettingsRepository
     * @param WebhookDataRepositoryInterface $webhookDataRepository
     */
    public function __construct(
        Unzer $unzer,
        ConnectionSettingsRepositoryInterface $connectionSettingsRepository,
        WebhookDataRepositoryInterface $webhookDataRepository
    ) {
        $this->unzer = $unzer;
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->webhookDataRepository = $webhookDataRepository;
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     */
    public function disconnect(): void
    {
        $this->unzer->deleteAllWebhooks();
        $this->webhookDataRepository->deleteWebhookData();
        $this->connectionSettingsRepository->deleteConnectionSettings();
    }
}
