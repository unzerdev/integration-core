<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\Connection\Services;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookSettings as WebhookSettingsEntity;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFound;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\EncryptionFailedException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidKeypairException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PrivateKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PublicKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookSettingsRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\EncryptorMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\KeypairMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerMock;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Constants\WebhookEvents;
use UnzerSDK\Resources\Webhook;

/**
 * Class ConnectionSettingsServiceTest.
 *
 * @package BusinessLogic\Domain\Connection\Services
 */
class ConnectionSettingsServiceTest extends BaseTestCase
{
    /**
     * @var ConnectionService
     */
    public $service;

    /**
     * @var MemoryRepository
     */
    public $repository;

    /**
     * @var MemoryRepository
     */
    public $webhookDataRepository;
    private $unzerFactory;

    /**
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->unzerFactory = (new UnzerFactoryMock())->setMockUnzer(new UnzerMock('s-priv-test'));
        TestServiceRegister::registerService(ConnectionService::class, function () {
            return new ConnectionService(
                $this->unzerFactory,
                TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                TestServiceRegister::getService(WebhookSettingsRepositoryInterface::class),
                TestServiceRegister::getService(EncryptorInterface::class),
                TestServiceRegister::getService(WebhookUrlServiceInterface::class)
            );
        },);
        $this->repository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
        $this->webhookDataRepository = TestRepositoryRegistry::getRepository(WebhookSettingsEntity::getClassName());
        $this->service = TestServiceRegister::getService(ConnectionService::class);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testInvalidPrivateKey(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('s-pub-test', 'private'),
            new ConnectionData('s-pub-test', 's-priv-test')
        );
        $this->mockData('s-pub-test', 's-priv-test');
        $this->expectException(PrivateKeyInvalidException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
    }

    /**
     * @return void
     * @throws InvalidModeException
     */
    public function testInvalidPrivateKeyForLiveMode(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-test', 's-priv-test'),
            new ConnectionData('s-pub-test', 's-priv-test')
        );

        $this->expectException(PrivateKeyInvalidException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);
    }

    /**
     * @return void
     * @throws InvalidModeException
     */
    public function testInvalidPrivateKeyForSandboxMode(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('sandbox'),
            new ConnectionData('p-pub-test', 'p-priv-test'),
            new ConnectionData('s-pub-test', 'p-priv-test')
        );

        $this->expectException(PrivateKeyInvalidException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testInvalidPublicKey(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('publicKey', 'p-priv-test'),
            new ConnectionData('s-pub-test', 's-priv-test')
        );
        $this->mockData('p-pub-test', 'p-priv-test');
        $this->expectException(PublicKeyInvalidException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
    }

    /**
     * @return void
     * @throws InvalidModeException
     */
    public function testInvalidPublicKeyForSandboxMode(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('sandbox'),
            new ConnectionData('p-pub-test', 'p-priv-test'),
            new ConnectionData('p-pub-test', 's-priv-test')
        );

        $this->expectException(PublicKeyInvalidException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testInvalidPublicKeyForLiveMode(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('s-pub-test', 'p-priv-test'),
            new ConnectionData('p-pub-test', 'p-priv-test')
        );

        $this->expectException(PublicKeyInvalidException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testInvalidKeypair(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-test', 'p-priv-test'),
            new ConnectionData('s-pub-test', 's-priv-test')
        );
        $this->mockData('p-pub-test2', 'p-priv-test');
        $this->expectException(InvalidKeypairException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testSaveConnectionData(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-test', 'p-priv-test'),
            new ConnectionData('s-pub-test', 's-priv-test')
        );
        $this->mockData('p-pub-test', 'p-priv-test');

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($settings, $savedEntity->getConnectionSettings());
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testSaveConnectionDataEncryption(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $this->mockData('p-pub-live-test', 'p-priv-live-test');

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
        /** @var ConnectionSettings $connectionSettings */
        $connectionSettings = $this->repository->selectOne()->getConnectionSettings();

        self::assertEquals('p-pub-live-test.', $connectionSettings->getLiveConnectionData()->getPublicKey());
        self::assertEquals('p-priv-live-test.', $connectionSettings->getLiveConnectionData()->getPrivateKey());
        self::assertEquals('s-pub-sandbox-test.', $connectionSettings->getSandboxConnectionData()->getPublicKey());
        self::assertEquals('s-priv-sandbox-test.', $connectionSettings->getSandboxConnectionData()->getPrivateKey());
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testChangeConnectionDataModeToSandbox(): void
    {
        // arrange
        $liveSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test')
        );
        $sandboxSettings = new ConnectionSettings(
            Mode::parse('sandbox'),
            null,
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $this->mockData('p-pub-live-test', 'p-priv-live-test');
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$liveSettings]);
        $this->mockData('s-pub-sandbox-test', 's-priv-sandbox-test');

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$sandboxSettings]);

        // assert
        /** @var ConnectionSettings $connectionSettings */
        $connectionSettings = $this->repository->selectOne()->getConnectionSettings();

        self::assertEquals(Mode::sandbox(), $connectionSettings->getMode());
        self::assertEquals('p-pub-live-test.', $connectionSettings->getLiveConnectionData()->getPublicKey());
        self::assertEquals('p-priv-live-test.', $connectionSettings->getLiveConnectionData()->getPrivateKey());
        self::assertEquals('s-pub-sandbox-test.', $connectionSettings->getSandboxConnectionData()->getPublicKey());
        self::assertEquals('s-priv-sandbox-test.', $connectionSettings->getSandboxConnectionData()->getPrivateKey());
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testChangeConnectionDataModeToLive(): void
    {
        // arrange
        $liveSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test')
        );
        $sandboxSettings = new ConnectionSettings(
            Mode::parse('sandbox'),
            null,
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $this->mockData('s-pub-sandbox-test', 's-priv-sandbox-test');
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$sandboxSettings]);
        $this->mockData('p-pub-live-test', 'p-priv-live-test');

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$liveSettings]);

        // assert
        /** @var ConnectionSettings $connectionSettings */
        $connectionSettings = $this->repository->selectOne()->getConnectionSettings();

        self::assertEquals(Mode::live(), $connectionSettings->getMode());
        self::assertEquals('p-pub-live-test.', $connectionSettings->getLiveConnectionData()->getPublicKey());
        self::assertEquals('p-priv-live-test.', $connectionSettings->getLiveConnectionData()->getPrivateKey());
        self::assertEquals('s-pub-sandbox-test.', $connectionSettings->getSandboxConnectionData()->getPublicKey());
        self::assertEquals('s-priv-sandbox-test.', $connectionSettings->getSandboxConnectionData()->getPrivateKey());
    }

    /**
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testWebhookDataSaved(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $webhook1 = new Webhook();
        $webhook1->setUrl('https://test.com');
        $webhook1->setEvent(WebhookEvents::PAYMENT);
        $webhook2 = new Webhook();
        $webhook2->setUrl('https://test.com');
        $webhook2->setEvent(WebhookEvents::CHARGE);

        $this->mockData('p-pub-live-test', 'p-priv-live-test', [$webhook1, $webhook2]);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
        /** @var WebhookSettingsEntity $connectionSettings */
        $webhookData = $this->webhookDataRepository->selectOne()->getWebhookSettings();

        self::assertNotNull($webhookData);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testReRegistringWebhooksNoConnectionSettings()
    {
        // arrange
        $this->expectException(ConnectionSettingsNotFoundException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'reRegisterWebhooks'], [Mode::live()]);

        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testReRegistringWebhooksWebhookDataSaved(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->repository->save($settings);
        $webhook1 = new Webhook();
        $webhook1->setId('1');
        $webhook1->setUrl('https://test.com');
        $webhook1->setEvent(WebhookEvents::PAYMENT);
        $webhook2 = new Webhook();
        $webhook2->setId('2');
        $webhook2->setUrl('https://test.com');
        $webhook2->setEvent(WebhookEvents::CHARGE);
        $this->mockData('p-pub-live-test', 'p-priv-live-test', [$webhook1, $webhook2]);

        // act
        $webhookSettingsSaved = StoreContext::doWithStore('1', [$this->service, 'reRegisterWebhooks'], [Mode::live()]);

        // assert
        /** @var WebhookSettings $webhookSettings */
        $webhookSettings = $this->webhookDataRepository->selectOne()->getWebhookSettings();

        self::assertNotNull($webhookSettings);
        self::assertEquals('https://test.com', $webhookSettings->getLiveWebhookData()->getUrl());
        self::assertCount(2, $webhookSettings->getLiveWebhookData()->getEvents());
        self::assertEquals($webhookSettings, $webhookSettingsSaved);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testReRegistringWebhooksNoConnectionDataForMode(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            null
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->repository->save($settings);
        $webhook1 = new Webhook();
        $webhook1->setId('1');
        $webhook1->setUrl('https://test.com');
        $webhook1->setEvent(WebhookEvents::PAYMENT);
        $webhook2 = new Webhook();
        $webhook2->setId('2');
        $webhook2->setUrl('https://test.com');
        $webhook2->setEvent(WebhookEvents::CHARGE);
        $this->mockData('p-pub-live-test', 'p-priv-live-test', [$webhook1, $webhook2]);
        $this->expectException(ConnectionDataNotFound::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'reRegisterWebhooks'], [Mode::sandbox()]);

        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetWebhookDataNoData(): void
    {
        // arrange

        // act
        $webhookData = StoreContext::doWithStore('1', [$this->service, 'getWebhookSettings']);

        // assert

        self::assertNull($webhookData);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetWebhookData(): void
    {
        // arrange
        $webhookData = new WebhookData('https://test2.com', ['1', '2', '3'], ['2', '3', '3'], 'test');
        $webhookSettings = new WebhookSettings(
            Mode::live(),
            $webhookData
        );
        $entity = new WebhookSettingsEntity();
        $entity->setWebhookSettings($webhookSettings);
        $entity->setStoreId('1');
        $this->webhookDataRepository->save($entity);

        // act
        $fetchedWebhookSettings = StoreContext::doWithStore('1', [$this->service, 'getWebhookSettings']);

        // assert

        self::assertEquals($webhookSettings, $fetchedWebhookSettings);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testReRegistringWebhooksOldWebhookDataDeleted(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('s-pub-live-test', 's-priv-live-test'),
            new ConnectionData('p-pub-sandbox-test', 'p-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->repository->save($settings);

        $oldData = new WebhookSettingsEntity();
        $webhookData = new WebhookData('https://test2.com', ['1', '2', '3'], ['2', '3', '3'], 'test');
        $webhookSettings = new WebhookSettings(
            Mode::live(),
            $webhookData
        );
        $oldData->setWebhookSettings($webhookSettings);
        $oldData->setStoreId('1');
        $this->webhookDataRepository->save($oldData);

        $webhook1 = new Webhook();
        $webhook1->setUrl('https://test.com');
        $webhook1->setEvent(WebhookEvents::PAYMENT);
        $webhook2 = new Webhook();
        $webhook2->setUrl('https://test.com');
        $webhook2->setEvent(WebhookEvents::CHARGE);
        $this->mockData('p-pub-live-test', 'p-priv-live-test', [$webhook1, $webhook2]);

        // act
        StoreContext::doWithStore('1', [$this->service, 'reRegisterWebhooks'], [Mode::live()]);

        // assert
        /** @var WebhookSettingsEntity $connectionSettings */
        $webhookDataEntity = $this->webhookDataRepository->select();

        self::assertNotNull($webhookData);
        self::assertCount(1, $webhookDataEntity);
        self::assertEquals(
            'https://test.com', $webhookDataEntity[0]->getWebhookSettings()->getLiveWebhookData()->getUrl()
        );
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testGetActiveConnectionDataWhenModeIsLive(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $this->mockData('p-pub-live-test', 'p-priv-live-test');
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$connectionSettings]);

        // act
        $active = StoreContext::doWithStore('1', [$this->service, 'getActiveConnectionData']);

        // assert
        self::assertNotNull($active);
        self::assertEquals('p-pub-live-test.', $connectionSettings->getLiveConnectionData()->getPublicKey());
        self::assertEquals('p-priv-live-test.', $connectionSettings->getLiveConnectionData()->getPrivateKey());
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws Exception
     */
    public function testGetActiveConnectionDataWhenModeIsTest(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('sandbox'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $this->mockData('s-pub-sandbox-test', 's-priv-sandbox-test');
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$connectionSettings]);

        // act
        $active = StoreContext::doWithStore('1', [$this->service, 'getActiveConnectionData']);

        // assert
        self::assertNotNull($active);
        self::assertEquals('s-pub-sandbox-test.', $connectionSettings->getSandboxConnectionData()->getPublicKey());
        self::assertEquals('s-priv-sandbox-test.', $connectionSettings->getSandboxConnectionData()->getPrivateKey());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetActiveConnectionDataWhenNoSettings(): void
    {
        // arrange

        // act
        $active = StoreContext::doWithStore('1', [$this->service, 'getActiveConnectionData']);

        // assert
        self::assertNull($active);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testGetActiveMode(): void
    {
        // arrange
        $liveSettings = new ConnectionSettings(Mode::parse('live'));
        $sandboxSettings = new ConnectionSettings(Mode::parse('sandbox'));

        // act
        $liveMode = $this->service->getActiveMode($liveSettings);
        $sandboxMode = $this->service->getActiveMode($sandboxSettings);

        // assert
        self::assertEquals(Mode::live(), $liveMode);
        self::assertEquals(Mode::sandbox(), $sandboxMode);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testInitializeConnectionSkipsWebhookRegistrationWhenLiveWebhooksExist(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $existingWebhookData = new WebhookData('https://old.com', ['1', '2'], ['payment', 'charge'], 'test');
        $entity = new WebhookSettingsEntity();
        $entity->setWebhookSettings(new WebhookSettings(Mode::live(), $existingWebhookData));
        $entity->setStoreId('1');
        $this->webhookDataRepository->save($entity);
        $webhook = new Webhook();
        $webhook->setUrl('https://test.com');
        $webhook->setEvent(WebhookEvents::PAYMENT);
        $this->mockData('p-pub-live-test', 'p-priv-live-test', [$webhook]);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
        /** @var WebhookSettings $webhookSettings */
        $webhookSettings = $this->webhookDataRepository->selectOne()->getWebhookSettings();

        self::assertEquals($existingWebhookData, $webhookSettings->getLiveWebhookData());
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('registerMultipleWebhooks'));
        self::assertNotNull($this->repository->selectOne());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testInitializeConnectionSkipsWebhookRegistrationWhenSandboxWebhooksExist(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('sandbox'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $existingWebhookData = new WebhookData('https://old.com', ['1', '2'], ['payment', 'charge'], 'test');
        $entity = new WebhookSettingsEntity();
        $entity->setWebhookSettings(new WebhookSettings(Mode::sandbox(), null, $existingWebhookData));
        $entity->setStoreId('1');
        $this->webhookDataRepository->save($entity);
        $webhook = new Webhook();
        $webhook->setUrl('https://test.com');
        $webhook->setEvent(WebhookEvents::PAYMENT);
        $this->mockData('s-pub-sandbox-test', 's-priv-sandbox-test', [$webhook]);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
        /** @var WebhookSettings $webhookSettings */
        $webhookSettings = $this->webhookDataRepository->selectOne()->getWebhookSettings();

        self::assertEquals($existingWebhookData, $webhookSettings->getSandboxWebhookData());
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('registerMultipleWebhooks'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testInitializeConnectionRegistersWebhooksWhenOnlyOtherModeIsRegistered(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('sandbox'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $existingWebhookData = new WebhookData('https://old.com', ['1', '2'], ['payment', 'charge'], 'test');
        $entity = new WebhookSettingsEntity();
        $entity->setWebhookSettings(new WebhookSettings(Mode::live(), $existingWebhookData));
        $entity->setStoreId('1');
        $this->webhookDataRepository->save($entity);
        $webhook = new Webhook();
        $webhook->setId('3');
        $webhook->setUrl('https://test.com');
        $webhook->setEvent(WebhookEvents::PAYMENT);
        $this->mockData('s-pub-sandbox-test', 's-priv-sandbox-test', [$webhook]);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
        /** @var WebhookSettings $webhookSettings */
        $webhookSettings = $this->webhookDataRepository->selectOne()->getWebhookSettings();

        self::assertEquals($existingWebhookData, $webhookSettings->getLiveWebhookData());
        self::assertNotNull($webhookSettings->getSandboxWebhookData());
        self::assertEquals('https://test.com', $webhookSettings->getSandboxWebhookData()->getUrl());
        self::assertEquals(['3'], $webhookSettings->getSandboxWebhookData()->getIds());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testInitializeConnectionWebhookRegistrationFailureIsIgnored(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $this->mockData('p-pub-live-test', 'p-priv-live-test');
        $this->unzerFactory->getMockUnzer()->setThrowOnRegisterWebhooks(true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);

        // assert
        self::assertNull($this->webhookDataRepository->selectOne());
        self::assertNotNull($this->repository->selectOne());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testReRegistringWebhooksSandboxModeUpdatesExistingSettings(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('sandbox'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->repository->save($settings);

        $liveWebhookData = new WebhookData('https://live.com', ['1', '2'], ['payment', 'charge'], 'test');
        $sandboxWebhookData = new WebhookData('https://old-sandbox.com', ['3'], ['payment'], 'test');
        $oldData = new WebhookSettingsEntity();
        $oldData->setWebhookSettings(new WebhookSettings(Mode::sandbox(), $liveWebhookData, $sandboxWebhookData));
        $oldData->setStoreId('1');
        $this->webhookDataRepository->save($oldData);

        $webhook = new Webhook();
        $webhook->setId('4');
        $webhook->setUrl('https://test.com');
        $webhook->setEvent(WebhookEvents::CHARGE);
        $this->mockData('s-pub-sandbox-test', 's-priv-sandbox-test', [$webhook]);

        // act
        $webhookSettingsSaved = StoreContext::doWithStore('1', [$this->service, 'reRegisterWebhooks'], [Mode::sandbox()]);

        // assert
        /** @var WebhookSettings $webhookSettings */
        $webhookSettings = $this->webhookDataRepository->selectOne()->getWebhookSettings();

        self::assertCount(1, $this->webhookDataRepository->select());
        self::assertEquals($liveWebhookData, $webhookSettings->getLiveWebhookData());
        self::assertEquals('https://test.com', $webhookSettings->getSandboxWebhookData()->getUrl());
        self::assertEquals(['4'], $webhookSettings->getSandboxWebhookData()->getIds());
        self::assertEquals($webhookSettings, $webhookSettingsSaved);
        $deleteCalls = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('deleteWebhook');
        self::assertCount(1, $deleteCalls);
        self::assertEquals('3', $deleteCalls[0]['webhookId']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteWebhooks(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->repository->save($settings);

        $liveWebhookData = new WebhookData('https://live.com', ['1', '2'], ['payment', 'charge'], 'test');
        $sandboxWebhookData = new WebhookData('https://sandbox.com', ['3'], ['payment'], 'test');
        $entity = new WebhookSettingsEntity();
        $entity->setWebhookSettings(new WebhookSettings(Mode::live(), $liveWebhookData, $sandboxWebhookData));
        $entity->setStoreId('1');
        $this->webhookDataRepository->save($entity);
        $this->mockData('p-pub-live-test', 'p-priv-live-test');

        // act
        StoreContext::doWithStore('1', [$this->service, 'deleteWebhooks']);

        // assert
        self::assertEmpty($this->webhookDataRepository->select());
        $deleteCalls = $this->unzerFactory->getMockUnzer()->getMethodCallHistory('deleteWebhook');
        self::assertCount(3, $deleteCalls);
        self::assertEquals(['1', '2', '3'], array_column($deleteCalls, 'webhookId'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteWebhooksApiErrorIsIgnored(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->repository->save($settings);

        $liveWebhookData = new WebhookData('https://live.com', ['1'], ['payment'], 'test');
        $sandboxWebhookData = new WebhookData('https://sandbox.com', ['2'], ['payment'], 'test');
        $entity = new WebhookSettingsEntity();
        $entity->setWebhookSettings(new WebhookSettings(Mode::live(), $liveWebhookData, $sandboxWebhookData));
        $entity->setStoreId('1');
        $this->webhookDataRepository->save($entity);
        $this->mockData('p-pub-live-test', 'p-priv-live-test');
        $this->unzerFactory->getMockUnzer()->setThrowOnDeleteWebhook(true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'deleteWebhooks']);

        // assert
        self::assertEmpty($this->webhookDataRepository->select());
        self::assertCount(2, $this->unzerFactory->getMockUnzer()->getMethodCallHistory('deleteWebhook'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteWebhooksNoData(): void
    {
        // arrange

        // act
        StoreContext::doWithStore('1', [$this->service, 'deleteWebhooks']);

        // assert
        self::assertEmpty($this->webhookDataRepository->select());
        self::assertEmpty($this->unzerFactory->getMockUnzer()->getMethodCallHistory('deleteWebhook'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteConnectionSettings(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->repository->save($settings);

        // act
        StoreContext::doWithStore('1', [$this->service, 'deleteConnectionSettings']);

        // assert
        self::assertEmpty($this->repository->select());
        self::assertNull(StoreContext::doWithStore('1', [$this->service, 'getConnectionSettings']));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetConnectedStoreIds(): void
    {
        // arrange
        foreach (['1', '2'] as $storeId) {
            $settings = new ConnectionSettingsEntity();
            $settings->setConnectionSettings(new ConnectionSettings(
                Mode::parse('live'),
                new ConnectionData('p-pub-live-test', 'p-priv-live-test')
            ));
            $settings->setStoreId($storeId);
            $this->repository->save($settings);
        }

        // act
        $storeIds = StoreContext::doWithStore('1', [$this->service, 'getConnectedStoreIds']);

        // assert
        self::assertEquals(['1', '2'], $storeIds);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetConnectedStoreIdsNoConnections(): void
    {
        // arrange

        // act
        $storeIds = StoreContext::doWithStore('1', [$this->service, 'getConnectedStoreIds']);

        // assert
        self::assertEquals([], $storeIds);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testInvalidPrivateKeyIsNotExposedInExceptionMessageOrTrace(): void
    {
        // arrange
        $privateKey = 'p-priv-secret-value';
        $settings = new ConnectionSettings(
            Mode::parse('sandbox'),
            null,
            new ConnectionData('s-pub-test', $privateKey)
        );
        $thrown = null;

        // act
        try {
            StoreContext::doWithStore('1', [$this->service, 'initializeConnection'], [$settings]);
        } catch (PrivateKeyInvalidException $exception) {
            $thrown = $exception;
        }

        // assert
        self::assertNotNull($thrown);
        self::assertStringNotContainsString($privateKey, $thrown->getMessage());
        self::assertStringNotContainsString($privateKey, $thrown->getTraceAsString());
        self::assertStringNotContainsString($privateKey, (string)$thrown);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testInitializeConnectionEncryptionFailureDoesNotExposeKey(): void
    {
        // arrange
        $privateKey = 'p-priv-live-test';
        $settings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', $privateKey),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $this->mockData('p-pub-live-test', $privateKey);
        $encryptor = new EncryptorMock();
        $encryptor->setThrowOnEncrypt(true);
        $service = $this->createServiceWithEncryptor($encryptor);
        $thrown = null;

        // act
        try {
            StoreContext::doWithStore('1', [$service, 'initializeConnection'], [$settings]);
        } catch (EncryptionFailedException $exception) {
            $thrown = $exception;
        }

        // assert
        self::assertNotNull($thrown);
        self::assertNull($thrown->getPrevious());
        self::assertEquals('connection.encryptionFailed', $thrown->getTranslatableLabel()->getCode());
        self::assertStringNotContainsString($privateKey, $thrown->getMessage());
        self::assertStringNotContainsString($privateKey, $thrown->getTraceAsString());
        self::assertStringNotContainsString($privateKey, (string)$thrown);
        self::assertNull($this->repository->selectOne());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetConnectionSettingsDecryptionFailureDoesNotExposeKey(): void
    {
        // arrange
        $privateKey = 'p-priv-live-test';
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', $privateKey),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->repository->save($settings);
        $encryptor = new EncryptorMock();
        $encryptor->setThrowOnDecrypt(true);
        $service = $this->createServiceWithEncryptor($encryptor);
        $thrown = null;

        // act
        try {
            StoreContext::doWithStore('1', [$service, 'getConnectionSettings']);
        } catch (EncryptionFailedException $exception) {
            $thrown = $exception;
        }

        // assert
        self::assertNotNull($thrown);
        self::assertNull($thrown->getPrevious());
        self::assertEquals('connection.decryptionFailed', $thrown->getTranslatableLabel()->getCode());
        self::assertStringNotContainsString($privateKey, $thrown->getMessage());
        self::assertStringNotContainsString($privateKey, $thrown->getTraceAsString());
        self::assertStringNotContainsString($privateKey, (string)$thrown);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetActiveConnectionDataDecryptionFailure(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('p-pub-live-test', 'p-priv-live-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->repository->save($settings);
        $encryptor = new EncryptorMock();
        $encryptor->setThrowOnDecrypt(true);
        $service = $this->createServiceWithEncryptor($encryptor);
        $this->expectException(EncryptionFailedException::class);

        // act
        StoreContext::doWithStore('1', [$service, 'getActiveConnectionData']);

        // assert
    }

    /**
     * @param EncryptorInterface $encryptor
     *
     * @return ConnectionService
     */
    private function createServiceWithEncryptor(EncryptorInterface $encryptor): ConnectionService
    {
        return new ConnectionService(
            $this->unzerFactory,
            TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
            TestServiceRegister::getService(WebhookSettingsRepositoryInterface::class),
            $encryptor,
            TestServiceRegister::getService(WebhookUrlServiceInterface::class)
        );
    }

    /**
     * @return void
     */
    private function mockData(string $publicKey, string $privateKey, array $webhooks = [])
    {
        $keypair = new KeypairMock();
        $keypair->setPublicKey($publicKey);
        $unzerMock = new UnzerMock($privateKey);
        $unzerMock->setKeypair($keypair);
        $unzerMock->setWebhooks($webhooks);
        $this->unzerFactory->setMockUnzer($unzerMock);
    }
}
