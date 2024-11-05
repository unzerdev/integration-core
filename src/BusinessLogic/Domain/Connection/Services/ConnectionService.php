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
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookSettingsRepositoryInterface;
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
    private UnzerFactory $unzerFactory;

    /** @var ConnectionSettingsRepositoryInterface */
    private ConnectionSettingsRepositoryInterface $connectionSettingsRepository;

    /** @var WebhookSettingsRepositoryInterface */
    private WebhookSettingsRepositoryInterface $webhookDataRepository;

    /** @var EncryptorInterface */
    private EncryptorInterface $encryptor;
    /** @var WebhookUrlServiceInterface */
    private WebhookUrlServiceInterface $webhookUrlService;

    /**
     * @param UnzerFactory $unzerFactory
     * @param ConnectionSettingsRepositoryInterface $connectionSettingsRepository
     * @param WebhookSettingsRepositoryInterface $webhookDataRepository
     * @param EncryptorInterface $encryptor
     * @param WebhookUrlServiceInterface $webhookUrlService
     */
    public function __construct(
        UnzerFactory $unzerFactory,
        ConnectionSettingsRepositoryInterface $connectionSettingsRepository,
        WebhookSettingsRepositoryInterface $webhookDataRepository,
        EncryptorInterface $encryptor,
        WebhookUrlServiceInterface $webhookUrlService
    ) {
        $this->unzerFactory = $unzerFactory;
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
        $unzer = $this->unzerFactory->makeUnzerAPI($connectionSettings->getActiveConnectionData());
        $this->validateKeypair($unzer, $connectionSettings);
        $webhookUrl = $this->webhookUrlService->getWebhookUrl();
        $unregisteredEvents = $this->getUnregisteredEvents($unzer, $webhookUrl);
        if (!empty($unregisteredEvents)) {
            $this->registerWebhooks($unzer, $unregisteredEvents, $webhookUrl, $connectionSettings->getMode());
        }

        $this->saveConnectionSettings($connectionSettings);
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
     * @return WebhookSettings|null
     */
    public function getWebhookSettings(): ?WebhookSettings
    {
        return $this->webhookDataRepository->getWebhookSettings();
    }

    /**
     * @return ?WebhookSettings
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function reRegisterWebhooks(): ?WebhookSettings
    {
        $connectionSettings = $this->getConnectionSettings();

        if (!$connectionSettings) {
            throw new ConnectionSettingsNotFoundException(
                new TranslatableLabel('Connection settings not found.',
                    'connectionSettings.notFound')
            );
        }

        $unzer = $this->unzerFactory->makeUnzerAPI($connectionSettings->getActiveConnectionData());
        $this->deleteWebhooks();
        $this->registerWebhooks(
            $unzer,
            SupportedWebhookEvents::SUPPORTED_WEBHOOK_EVENTS,
            $this->webhookUrlService->getWebhookUrl(),
            $connectionSettings->getMode()
        );

        return $this->getWebhookSettings();
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     */
    public function deleteWebhooks(): void
    {
        if (!($webhookSettings = $this->getWebhookSettings()) ||
            !($connectionSettings = $this->getConnectionSettings())) {
            return;
        }

        if ($webhookSettings->getLiveWebhookData()) {
            foreach ($webhookSettings->getLiveWebhookData()->getIds() as $webhookId) {
                try {
                    $this->unzerFactory->makeUnzerAPI($connectionSettings->getLiveConnectionData())->deleteWebhook($webhookId);
                } catch (UnzerApiException $e) {
                    // if webhook id does not exist on API continue
                }
            }
        }

        if ($webhookSettings->getSandboxWebhookData()) {
            foreach ($webhookSettings->getSandboxWebhookData()->getIds() as $webhookId) {
                try {
                    $this->unzerFactory->makeUnzerAPI($connectionSettings->getSandboxConnectionData())->deleteWebhook($webhookId);
                } catch (UnzerApiException $e) {
                    // if webhook id does not exist on API continue
                }
            }
        }

        $this->webhookDataRepository->deleteWebhookSettings();
    }

    /**
     * @return void
     */
    public function deleteConnectionSettings(): void
    {
        $this->connectionSettingsRepository->deleteConnectionSettings();
    }

    /**
     * @return string[]
     */
    public function getConnectedStoreIds(): array
    {
        return $this->connectionSettingsRepository->getAllConnectedStoreIds();
    }

    /**
     * @param Unzer $unzer
     * @param string $webhookUrl
     *
     * @return array
     *
     * @throws UnzerApiException
     */
    private function getUnregisteredEvents(Unzer $unzer, string $webhookUrl): array
    {
        $webhooks = [];

        foreach ($unzer->fetchAllWebhooks() as $registeredWebhook) {
            if ($registeredWebhook->getUrl() === $webhookUrl) {
                $webhooks[] = $registeredWebhook;
            }
        }

        $supportedEvents = SupportedWebhookEvents::SUPPORTED_WEBHOOK_EVENTS;
        if (empty($webhooks)) {
            return $supportedEvents;
        }

        $events = [];
        $registeredEvents = array_map(fn(Webhook $webhook) => $webhook->getEvent(), $webhooks);
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
     * @param string $webhookUrl
     * @param Mode $mode
     *
     * @return void
     *
     * @throws UnzerApiException
     */
    private function registerWebhooks(Unzer $unzer, array $events, string $webhookUrl, Mode $mode): void
    {
        $webhooks = $unzer->registerMultipleWebhooks($webhookUrl, $events);

        if (!empty($webhooks)) {
            $webhookData = WebhookData::fromBatch($webhooks);
            $this->saveWebhookSettings($webhookData, $mode);
        }
    }

    /**
     * @param WebhookData $webhookData
     * @param Mode $mode
     *
     * @return void
     */
    private function saveWebhookSettings(WebhookData $webhookData, Mode $mode): void
    {
        $existingSettings = $this->webhookDataRepository->getWebhookSettings();

        if (!$existingSettings) {
            $webhookSettings = new WebhookSettings(
                $mode,
                $mode->equal(Mode::live()) ? $webhookData : null,
                $mode->equal(Mode::sandbox()) ? $webhookData : null);
            $this->webhookDataRepository->setWebhookSettings($webhookSettings);

            return;
        }

        $existingWebhookData = $mode->equal(Mode::live()) ?
            $existingSettings->getLiveWebhookData() :
            $existingSettings->getSandboxWebhookData();

        $webhookDataToInsert = null;

        if ($existingWebhookData) {
            $existingWebhookData->setIds(array_merge($existingWebhookData->getIds(), $webhookData->getIds()));
            $existingWebhookData->setEvents(array_merge($webhookData->getEvents(), $existingWebhookData->getEvents()));
            $webhookDataToInsert = $existingWebhookData;
        }

        if(!$existingWebhookData) {
            $webhookDataToInsert = $webhookData;
        }

        $mode->equal(Mode::live()) ?
            $existingSettings->setLiveWebhookData($webhookDataToInsert) :
            $existingSettings->setSandboxWebhookData($webhookDataToInsert);

        $this->webhookDataRepository->setWebhookSettings($existingSettings);
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
        $mode = $connectionSettings->getMode();
        $this->validatePrivateKey($connectionSettings->getActiveConnectionData()->getPrivateKey(), $mode);
        $this->validatePublicKey($connectionSettings->getActiveConnectionData()->getPublicKey(), $mode);
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
     * @param Mode $mode
     *
     * @return void
     *
     * @throws PrivateKeyInvalidException
     */
    private function validatePrivateKey(string $privateKey, Mode $mode): void
    {
        if (!PrivateKeyValidator::validate($privateKey)) {
            throw new PrivateKeyInvalidException(
                new TranslatableLabel("Private key: {$privateKey} is invalid.",
                    'connection.invalidPrivateKey')
            );
        }

        if ($mode->equal(Mode::live()) && !$this->isLiveKey($privateKey)) {
            throw new PrivateKeyInvalidException(
                new TranslatableLabel("The private key: {$privateKey} does not match live mode.",
                    'connection.invalidPrivateKeyForMode')
            );
        }

        if ($mode->equal(Mode::sandbox()) && !$this->isSandboxKey($privateKey)) {
            throw new PrivateKeyInvalidException(
                new TranslatableLabel("The public key: {$privateKey} does not match sandbox mode.",
                    'connection.invalidPrivateKeyForMode')
            );
        }
    }

    /**
     * @param string $publicKey
     * @param Mode $mode
     *
     * @return void
     *
     * @throws PublicKeyInvalidException
     */
    private function validatePublicKey(string $publicKey, Mode $mode): void
    {
        if (!PublicKeyValidator::validate($publicKey)) {
            throw new PublicKeyInvalidException(
                new TranslatableLabel("Public key: {$publicKey} is invalid.",
                    'connection.invalidPublicKey')
            );
        }

        if ($mode->equal(Mode::live()) && !$this->isLiveKey($publicKey)) {
            throw new PublicKeyInvalidException(
                new TranslatableLabel("The private key: {$publicKey} does not match live mode.",
                    'connection.invalidPublicKeyForMode')
            );
        }

        if ($mode->equal(Mode::sandbox()) && !$this->isSandboxKey($publicKey)) {
            throw new PublicKeyInvalidException(
                new TranslatableLabel("The public key: {$publicKey} does not match sandbox mode.",
                    'connection.invalidPublicKeyForMode')
            );
        }
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function isLiveKey(string $key): bool
    {
        return strpos($key, 'p') === 0;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function isSandboxKey(string $key): bool
    {
        return strpos($key, 's') === 0;
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
