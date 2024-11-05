<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\Connection;

use DateTime;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\GetConnectionDataRequest;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\ReconnectRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidKeypairException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PrivateKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PublicKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookSettingsRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\ConnectionServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\DisconnectServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class ConnectionControllerTest.
 *
 * @package BusinessLogic\AdminAPI\Connection
 */
class ConnectionControllerTest extends BaseTestCase
{
    /**
     * @var ConnectionServiceMock
     */
    private ConnectionServiceMock $connectionService;

    /**
     * @var DisconnectServiceMock
     */
    private DisconnectServiceMock $disconnectService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connectionService = new ConnectionServiceMock(
            new UnzerFactoryMock(),
            TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
            TestServiceRegister::getService(WebhookSettingsRepositoryInterface::class),
            TestServiceRegister::getService(EncryptorInterface::class),
            TestServiceRegister::getService(WebhookUrlServiceInterface::class)
        );

        TestServiceRegister::registerService(
            ConnectionService::class, function () {
            return $this->connectionService;
        });

        $this->disconnectService = new DisconnectServiceMock(
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class),
            TestServiceRegister::getService(PaymentPageSettingsRepositoryInterface::class),
            TestServiceRegister::getService(PaymentStatusMapRepositoryInterface::class),
            TestServiceRegister::getService(TransactionHistoryRepositoryInterface::class)
        );

        TestServiceRegister::registerService(
            DisconnectService::class, function () {
            return $this->disconnectService;
        });
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidKeypairException
     * @throws InvalidModeException
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     */

    public function testSuccessfulLiveConnection(): void
    {
        // Arrange
        $connectionRequest = new ConnectionRequest('live', 'pKey', 'privKey');

        // Act
        $response = AdminAPI::get()->connection('1')->connect($connectionRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidKeypairException
     * @throws InvalidModeException
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     */
    public function testSuccessfulSandboxConnection(): void
    {
        // Arrange
        $connectionRequest = new ConnectionRequest('sandbox', 'pKey', 'privKey');

        // Act
        $response = AdminAPI::get()->connection('1')->connect($connectionRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidKeypairException
     * @throws InvalidModeException
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     */
    public function testConnectResponseToArray(): void
    {
        // Arrange
        $connectionRequest = new ConnectionRequest('sandbox', 'pKey', 'privKey');

        // Act
        $response = AdminAPI::get()->connection('1')->connect($connectionRequest);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testGetConnectionSettingsNoSettings(): void
    {
        // Arrange
        $connectionRequest = new GetConnectionDataRequest('live');

        // Act
        $response = AdminAPI::get()->connection('1')->getConnectionData($connectionRequest);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testGetLiveData(): void
    {
        // Arrange
        $connectionRequest = new GetConnectionDataRequest('live');
        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        // Act
        $response = AdminAPI::get()->connection('1')->getConnectionData($connectionRequest);

        // Assert
        self::assertEquals('publicKeyTest', $response->toArray()['publicKey']);
        self::assertEquals('privateKeyTest', $response->toArray()['privateKey']);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testGetSandboxData(): void
    {
        // Arrange
        $connectionRequest = new GetConnectionDataRequest('sandbox');
        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::sandbox(),
                null,
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        // Act
        $response = AdminAPI::get()->connection('1')->getConnectionData($connectionRequest);

        // Assert
        self::assertEquals('publicKeyTest', $response->toArray()['publicKey']);
        self::assertEquals('privateKeyTest', $response->toArray()['privateKey']);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testGetLiveDataNoData(): void
    {
        // Arrange
        $connectionRequest = new GetConnectionDataRequest('live');
        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                null,
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        // Act
        $response = AdminAPI::get()->connection('1')->getConnectionData($connectionRequest);

        // Assert
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testGetSandboxDataNoData(): void
    {
        // Arrange
        $connectionRequest = new GetConnectionDataRequest('sandbox');
        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );

        // Act
        $response = AdminAPI::get()->connection('1')->getConnectionData($connectionRequest);

        // Assert
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function testReRegistringWebhooksSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->connection('1')->reRegisterWebhooks();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function testReRegistringWebhooksToArray(): void
    {
        // Arrange
        $date = new DateTime('2024-10-03 14:30:00');
        $time = $date->format('F d, Y H:i');
        $settings = new WebhookSettings(
            Mode::live(),
            new WebhookData('test.com', ['1', '2'], ['test', 'test2'], $time)
        );
        $this->connectionService->setWebhookSettings($settings);

        // Act
        $response = AdminAPI::get()->connection('1')->reRegisterWebhooks();

        // Assert
        self::assertArrayHasKey('webhookData', $response->toArray());
        self::assertArrayNotHasKey('connectionData', $response->toArray());
        self::assertEquals('1, 2', $response->toArray()['webhookData']['webhookID']);
        self::assertEquals('test.com', $response->toArray()['webhookData']['webhookUrl']);
        self::assertEquals('test, test2', $response->toArray()['webhookData']['events']);
        self::assertEquals('October 03, 2024 14:30', $response->toArray()['webhookData']['registrationDate']);
    }

    /**
     * @return void
     */
    public function testGetCredentialsSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->connection('1')->getCredentials();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testGetCredentialsToArrayEmpty(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->connection('1')->getCredentials();

        // Assert
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     */
    public function testGetCredentialsToArrayOnlyLive(): void
    {
        // Arrange
        $date = new DateTime('2024-10-03 14:30:00');
        $time = $date->format('F d, Y H:i');
        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );
        $settings = new WebhookSettings(
            Mode::live(),
            new WebhookData('test.com', ['1', '2'], ['test', 'test2'], $time)
        );

        $this->connectionService->setWebhookSettings($settings);

        // Act
        $response = AdminAPI::get()->connection('1')->getCredentials();

        // Assert
        $array = $response->toArray();
        self::assertEquals('publicKeyTest', $array['live']['connectionData']['publicKey']);
        self::assertEquals('privateKeyTest', $array['live']['connectionData']['privateKey']);
        self::assertEquals('October 03, 2024 14:30', $array['live']['webhookData']['registrationDate']);
        self::assertEquals('1, 2', $array['live']['webhookData']['webhookID']);
        self::assertEquals('test, test2', $array['live']['webhookData']['events']);
        self::assertEquals('test.com', $array['live']['webhookData']['webhookUrl']);
    }

    /**
     * @return void
     */
    public function testGetCredentialsToArrayOnlySandbox(): void
    {
        // Arrange
        $date = new DateTime('2024-10-03 14:30:00');
        $time = $date->format('F d, Y H:i');
        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::sandbox(),
                null,
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );
        $settings = new WebhookSettings(
            Mode::sandbox(),
            null,
            new WebhookData('test.com', ['1', '2'], ['test', 'test2'], $time)
        );

        $this->connectionService->setWebhookSettings($settings);

        // Act
        $response = AdminAPI::get()->connection('1')->getCredentials();

        // Assert
        $array = $response->toArray();
        self::assertEquals('publicKeyTest', $array['sandbox']['connectionData']['publicKey']);
        self::assertEquals('privateKeyTest', $array['sandbox']['connectionData']['privateKey']);
        self::assertEquals('October 03, 2024 14:30', $array['sandbox']['webhookData']['registrationDate']);
        self::assertEquals('1, 2', $array['sandbox']['webhookData']['webhookID']);
        self::assertEquals('test, test2', $array['sandbox']['webhookData']['events']);
        self::assertEquals('test.com', $array['sandbox']['webhookData']['webhookUrl']);
    }

    /**
     * @return void
     */
    public function testGetCredentialsToArray(): void
    {
        // Arrange
        $date = new DateTime('2024-10-03 14:30:00');
        $time = $date->format('F d, Y H:i');
        $this->connectionService->setConnectionSettings(
            new ConnectionSettings(
                Mode::sandbox(),
                new ConnectionData('publicKeyTest', 'privateKeyTest'),
                new ConnectionData('publicKeyTest', 'privateKeyTest')
            )
        );
        $settings = new WebhookSettings(
            Mode::sandbox(),
            new WebhookData('test.com', ['1', '2'], ['test', 'test2'], $time),
            new WebhookData('test.com', ['1', '2'], ['test', 'test2'], $time)
        );

        $this->connectionService->setWebhookSettings($settings);

        // Act
        $response = AdminAPI::get()->connection('1')->getCredentials();

        // Assert
        $array = $response->toArray();
        self::assertEquals('publicKeyTest', $array['sandbox']['connectionData']['publicKey']);
        self::assertEquals('privateKeyTest', $array['sandbox']['connectionData']['privateKey']);
        self::assertEquals('October 03, 2024 14:30', $array['sandbox']['webhookData']['registrationDate']);
        self::assertEquals('1, 2', $array['sandbox']['webhookData']['webhookID']);
        self::assertEquals('test, test2', $array['sandbox']['webhookData']['events']);
        self::assertEquals('test.com', $array['sandbox']['webhookData']['webhookUrl']);
        self::assertEquals('publicKeyTest', $array['live']['connectionData']['publicKey']);
        self::assertEquals('privateKeyTest', $array['live']['connectionData']['privateKey']);
        self::assertEquals('October 03, 2024 14:30', $array['live']['webhookData']['registrationDate']);
        self::assertEquals('1, 2', $array['live']['webhookData']['webhookID']);
        self::assertEquals('test, test2', $array['live']['webhookData']['events']);
        self::assertEquals('test.com', $array['live']['webhookData']['webhookUrl']);
    }

    /**
     * @throws InvalidModeException
     * @throws PublicKeyInvalidException
     * @throws QueryFilterInvalidParamException
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     * @throws InvalidKeypairException
     * @throws PrivateKeyInvalidException
     */
    public function testSuccessfulReconnect(): void
    {
        // Arrange
        $reconnectionRequest = new ReconnectRequest('sandbox', 'pKey', 'privKey');

        // Act
        $response = AdminAPI::get()->connection('1')->reconnect($reconnectionRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidKeypairException
     * @throws InvalidModeException
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     * @throws QueryFilterInvalidParamException
     * @throws UnzerApiException
     */
    public function testReconnectDeleteData(): void
    {
        // Arrange
        $reconnectionRequest = new ReconnectRequest('live', 'pKey', 'privKey', true);

        // Act
        $response = AdminAPI::get()->connection('1')->reconnect($reconnectionRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }
}
