<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Enums\SupportedWebhookEvents;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidKeypairException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PrivateKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PublicKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Webhook;
use UnzerSDK\Unzer;
use UnzerSDK\Validators\PrivateKeyValidator;
use UnzerSDK\Validators\PublicKeyValidator;

/**
 * Class ConnectionService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Connection\Services
 */
class ConnectionService
{
    /** @var ConnectionSettingsRepositoryInterface */
    private ConnectionSettingsRepositoryInterface $connectionSettingsRepository;

    /** @var WebhookDataRepositoryInterface */
    private WebhookDataRepositoryInterface $webhookDataRepository;

    /** @var EncryptorInterface */
    private EncryptorInterface $encryptor;

    /** @var WebhookUrlServiceInterface */
    private WebhookUrlServiceInterface $webhookUrlService;

    /**
     * @param ConnectionSettingsRepositoryInterface $connectionSettingsRepository
     * @param WebhookDataRepositoryInterface $webhookDataRepository
     * @param EncryptorInterface $encryptor
     * @param WebhookUrlServiceInterface $webhookUrlService
     */
    public function __construct(
        ConnectionSettingsRepositoryInterface $connectionSettingsRepository,
        WebhookDataRepositoryInterface $webhookDataRepository,
        EncryptorInterface $encryptor,
        WebhookUrlServiceInterface $webhookUrlService
    ) {
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->webhookDataRepository = $webhookDataRepository;
        $this->encryptor = $encryptor;
        $this->webhookUrlService = $webhookUrlService;
    }

    /**
     * Validates keys, keypair, register webhooks, save webhook entity and save connection settings entity.
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidKeypairException
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     * @throws UnzerApiException
     */
    public function initializeConnection(ConnectionSettings $connectionSettings): void
    {
        $this->validateKeys($connectionSettings);
        $unzer = UnzerFactory::getInstance()->makeUnzerAPI($connectionSettings);
        $this->validateKeypair($unzer, $connectionSettings);
        $unregisteredEvents = $this->getUnregisteredEvents($unzer);
        if (!empty($unregisteredEvents)) {
            $this->registerWebhooks($unzer, $unregisteredEvents);
        }

        $this->saveConnectionSettings($connectionSettings);
    }

    /**
     * Check if user is loggedIn for specific store.
     *
     * @param Mode $mode
     *
     * @return bool
     */
    public function isLoggedIn(Mode $mode): bool
    {
        $connectionSettings = $this->getConnectionSettings();

        if (!$connectionSettings) {
            return false;
        }

        if($mode->equal(Mode::live())) {
            return $connectionSettings->getLiveConnectionData() !== null;
        }

        return $connectionSettings->getSandboxConnectionData() !== null;
    }


    /**
     * @return ConnectionSettings|null
     */
    public function getConnectionSettings(): ?ConnectionSettings
    {
        $connectionSettings = $this->connectionSettingsRepository->getConnectionSettings();

        return $connectionSettings ? $this->decryptConnectionSettings($connectionSettings) : null;
    }

    /**
     * @return WebhookData|null
     */
    public function getWebhookData(): ?WebhookData
    {
        return $this->webhookDataRepository->getWebhookData();
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function reRegisterWebhooks(): void
    {
        $connectionSettings = $this->connectionSettingsRepository->getConnectionSettings();

        if (!$connectionSettings) {
            throw new ConnectionSettingsNotFoundException(
                new TranslatableLabel('Connection settings not found.',
                    'connectionSettings.notFound')
            );
        }

        $unzer = UnzerFactory::getInstance()->makeUnzerAPI($connectionSettings);
        $unzer->deleteAllWebhooks();
        $this->webhookDataRepository->deleteWebhookData();
        $this->registerWebhooks($unzer, SupportedWebhookEvents::SUPPORTED_WEBHOOK_EVENTS);
    }

    /**
     * @param Unzer $unzer
     *
     * @return array
     *
     * @throws UnzerApiException
     */
    private function getUnregisteredEvents(Unzer $unzer): array
    {
        $registeredWebhooks = $unzer->fetchAllWebhooks();
        $supportedEvents = SupportedWebhookEvents::SUPPORTED_WEBHOOK_EVENTS;
        if (empty($registeredWebhooks)) {
            return $supportedEvents;
        }

        $events = [];
        $registeredEvents = array_map(fn(Webhook $webhook) => $webhook->getEvent(), $registeredWebhooks);
        foreach ($supportedEvents as $supportedEvent) {
            if (in_array($supportedEvent, $registeredEvents)) {
                continue;
            }
            $events[] = $supportedEvent;
        }

        return $events;
    }

    /**
     * Registers all supported webhook events.
     *
     * @param Unzer $unzer
     * @param array $events
     *
     * @return void
     *
     * @throws UnzerApiException
     */
    private function registerWebhooks(Unzer $unzer, array $events): void
    {
        $webhookUrl = $this->webhookUrlService->getWebhookUrl();
        $webhooks = $unzer->registerMultipleWebhooks($webhookUrl, $events);

        if(!empty($webhooks)){
            $webhookData = WebhookData::fromBatch($webhooks);
            $this->saveWebhookData($webhookData);
        }
    }

    /**
     * @param WebhookData $webhookData
     *
     * @return void
     */
    private function saveWebhookData(WebhookData $webhookData): void
    {
        $existingData = $this->webhookDataRepository->getWebhookData();

        if(!$existingData) {
            $this->webhookDataRepository->setWebhookData($webhookData);

            return;
        }

        $webhookData->setIds(array_merge($webhookData->getIds(), $existingData->getIds()));
        $webhookData->setEvents(array_merge($webhookData->getEvents(), $existingData->getEvents()));
        $this->webhookDataRepository->setWebhookData($webhookData);
    }

    /**
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     *
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     */
    private function validateKeys(ConnectionSettings $connectionSettings): void
    {
        $this->validatePrivateKey($connectionSettings->getActiveConnectionData()->getPrivateKey());
        $this->validatePublicKey($connectionSettings->getActiveConnectionData()->getPublicKey());
    }

    /**
     * @param Unzer $unzer
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     *
     * @throws InvalidKeypairException
     * @throws UnzerApiException
     */
    private function validateKeypair(Unzer $unzer, ConnectionSettings $connectionSettings): void
    {
        $keypair = $unzer->fetchKeypair();

        if ($keypair->getPublicKey() !== $connectionSettings->getActiveConnectionData()->getPublicKey()) {
            throw new InvalidKeypairException(
                new TranslatableLabel('Private Key does not match public key', 'connection.invalidKeypair')
            );
        }
    }

    /**
     * @param string $privateKey
     *
     * @return void
     *
     * @throws PrivateKeyInvalidException
     */
    private function validatePrivateKey(string $privateKey): void
    {
        if (!PrivateKeyValidator::validate($privateKey)) {
            throw new PrivateKeyInvalidException(
                new TranslatableLabel("Private key: {$privateKey} is invalid.",
                    'connection.invalidPrivateKey')
            );
        }
    }

    /**
     * @param string $publicKey
     *
     * @return void
     *
     * @throws PublicKeyInvalidException
     */
    private function validatePublicKey(string $publicKey): void
    {
        if (!PublicKeyValidator::validate($publicKey)) {
            throw new PublicKeyInvalidException(
                new TranslatableLabel("Public key: {$publicKey} is invalid.",
                    'connection.invalidPublicKey')
            );
        }
    }

    /**
     * Saves connection settings.
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     */
    private function saveConnectionSettings(ConnectionSettings $connectionSettings): void
    {
        $existingConnectionSettings = $this->getConnectionSettings();

        if (!$existingConnectionSettings) {
            $this->connectionSettingsRepository->setConnectionSettings($this->encryptConnectionSettings($connectionSettings));

            return;
        }

        if ($connectionSettings->getMode()->equal(Mode::sandbox())) {
            $existingConnectionSettings->setMode(Mode::sandbox());
            $existingConnectionSettings->setSandboxConnectionData($connectionSettings->getSandboxConnectionData()->clone());
        }

        if ($connectionSettings->getMode()->equal(Mode::live())) {
            $existingConnectionSettings->setMode(Mode::live());
            $existingConnectionSettings->setLiveConnectionData($connectionSettings->getLiveConnectionData()->clone());
        }

        $this->connectionSettingsRepository->setConnectionSettings($this->encryptConnectionSettings($existingConnectionSettings));
    }

    /**
     * Encrypts private and public key
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return ConnectionSettings
     */
    private function encryptConnectionSettings(ConnectionSettings $connectionSettings): ConnectionSettings
    {
        if ($connectionSettings->getSandboxConnectionData()) {
            $connectionSettings->setSandboxConnectionData(
                new ConnectionData(
                    $this->encryptor->encrypt($connectionSettings->getSandboxConnectionData()->getPublicKey()),
                    $this->encryptor->encrypt($connectionSettings->getSandboxConnectionData()->getPrivateKey())
                )
            );
        }

        if ($connectionSettings->getLiveConnectionData()) {
            $connectionSettings->setLiveConnectionData(
                new ConnectionData(
                    $this->encryptor->encrypt($connectionSettings->getLiveConnectionData()->getPublicKey()),
                    $this->encryptor->encrypt($connectionSettings->getLiveConnectionData()->getPrivateKey())
                )
            );
        }

        return $connectionSettings;
    }

    /**
     * Decrypts private and public key
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return ConnectionSettings
     */
    private function decryptConnectionSettings(ConnectionSettings $connectionSettings): ConnectionSettings
    {
        if ($connectionSettings->getSandboxConnectionData()) {
            $connectionSettings->setSandboxConnectionData(
                new ConnectionData(
                    $this->encryptor->decrypt($connectionSettings->getSandboxConnectionData()->getPublicKey()),
                    $this->encryptor->decrypt($connectionSettings->getSandboxConnectionData()->getPrivateKey())
                )
            );
        }

        if ($connectionSettings->getLiveConnectionData()) {
            $connectionSettings->setLiveConnectionData(
                new ConnectionData(
                    $this->encryptor->decrypt($connectionSettings->getLiveConnectionData()->getPublicKey()),
                    $this->encryptor->decrypt($connectionSettings->getLiveConnectionData()->getPrivateKey())
                )
            );
        }

        return $connectionSettings;
    }
}
