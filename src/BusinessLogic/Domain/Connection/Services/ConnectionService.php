<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Services;

use Unzer\Core\BusinessLogic\Domain\Connection\Enums\SupportedWebhookEvents;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFound;
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

        if (!$this->isWebhookRegistrationNecessary($connectionSettings->getMode())) {
            $this->registerWebhooks(
                $unzer,
                $this->webhookUrlService->getWebhookUrl(),
                $connectionSettings->getMode()
            );
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
     * @param Mode $mode
     *
     * @return ?WebhookSettings
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     * @throws ConnectionDataNotFound
     */
    public function reRegisterWebhooks(Mode $mode): ?WebhookSettings
    {
        $connectionSettings = $this->getConnectionSettings();

        if (!$connectionSettings) {
            throw new ConnectionSettingsNotFoundException(
                new TranslatableLabel('Connection settings not found.',
                    'connectionSettings.notFound')
            );
        }

        $connectionData = $mode->equal(Mode::live()) ?
            $connectionSettings->getLiveConnectionData() :
            $connectionSettings->getSandboxConnectionData();

        if (!$connectionData) {
            throw new ConnectionDataNotFound(
                new TranslatableLabel('Connection data for mode: ' . $mode->getMode() . 'not found.',
                    'connectionData.notFound')
            );
        }

        $unzer = $this->unzerFactory->makeUnzerAPI($connectionData);
        $this->deleteWebhooksForMode($mode);
        $this->registerWebhooks(
            $unzer,
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
        $this->deleteWebhooksForMode(Mode::live());
        $this->deleteWebhooksForMode(Mode::sandbox());

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

    private function isWebhookRegistrationNecessary(Mode $mode): bool
    {
        $webhookSettings = $this->webhookDataRepository->getWebhookSettings();

        if ($webhookSettings) {
            $webhookData = $mode->equal(Mode::live())
                ? $webhookSettings->getLiveWebhookData()
                : $webhookSettings->getSandboxWebhookData();

            return $webhookData !== null;
        }

        return false;
    }

    /**
     * @param Mode $mode
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     */
    private function deleteWebhooksForMode(Mode $mode): void
    {
        if (!($webhookSettings = $this->getWebhookSettings()) ||
            !($connectionSettings = $this->getConnectionSettings())) {
            return;
        }

        if ($mode->equal(Mode::live()) && $webhookSettings->getLiveWebhookData()) {
            foreach ($webhookSettings->getLiveWebhookData()->getIds() as $webhookId) {
                try {
                    $this->unzerFactory->makeUnzerAPI($connectionSettings->getLiveConnectionData())->deleteWebhook($webhookId);
                } catch (UnzerApiException $e) {
                    // if webhook id does not exist on API continue
                }
            }
        }

        if ($mode->equal(Mode::sandbox()) && $webhookSettings->getSandboxWebhookData()) {
            foreach ($webhookSettings->getSandboxWebhookData()->getIds() as $webhookId) {
                try {
                    $this->unzerFactory->makeUnzerAPI($connectionSettings->getSandboxConnectionData())->deleteWebhook($webhookId);
                } catch (UnzerApiException $e) {
                    // if webhook id does not exist on API continue
                }
            }
        }
    }

    /**
     * Registers all supported webhook events.
     *
     * @param Unzer $unzer
     * @param string $webhookUrl
     * @param Mode $mode
     *
     * @return void
     */
    private function registerWebhooks(Unzer $unzer, string $webhookUrl, Mode $mode): void
    {
        try {
            $webhooks = $unzer->registerMultipleWebhooks($webhookUrl, SupportedWebhookEvents::SUPPORTED_WEBHOOK_EVENTS);
        } catch (UnzerApiException $e) {
            // if webhook registration fails continue
            $webhooks = [];
        }

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

        $mode->equal(Mode::live()) ?
            $existingSettings->setLiveWebhookData($webhookData) :
            $existingSettings->setSandboxWebhookData($webhookData);

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
