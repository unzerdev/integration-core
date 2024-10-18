<?php

namespace Unzer\Core\BusinessLogic\Domain\Disconnect\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
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
     * @param UnzerFactory $unzerFactory
     * @param ConnectionSettingsRepositoryInterface $connectionSettingsRepository
     * @param WebhookDataRepositoryInterface $webhookDataRepository
     */
    public function __construct(
        UnzerFactory $unzerFactory,
        ConnectionSettingsRepositoryInterface $connectionSettingsRepository,
        WebhookDataRepositoryInterface $webhookDataRepository
    ) {
        $this->unzerFactory = $unzerFactory;
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->webhookDataRepository = $webhookDataRepository;
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function disconnect(): void
    {
        $this->unzerFactory->makeUnzerAPI()->deleteAllWebhooks();
        $this->webhookDataRepository->deleteWebhookData();
        $this->connectionSettingsRepository->deleteConnectionSettings();
    }
}
