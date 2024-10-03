<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\Disconnect\Services;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookData as WebhookDataEntity;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class DisconnectServiceTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectServiceTest extends BaseTestCase
{
    /**
     * @var DisconnectService
     */
    public $service;

    /**
     * @var MemoryRepository
     */
    public $webhookDataRepository;

    /**
     * @var MemoryRepository
     */
    public $connectionSettingsRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionSettingsRepository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
        $this->webhookDataRepository = TestRepositoryRegistry::getRepository(WebhookDataEntity::getClassName());
        $this->service = TestServiceRegister::getService(DisconnectService::class);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws Exception
     */
    public function testWebhookDataDeleted(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('s-pub-live-test', 's-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->connectionSettingsRepository->save($settings);

        $oldData = new WebhookDataEntity();
        $webhookData = new WebhookData('https://test2.com', ['1', '2', '3'], ['2', '3', '3'], 'test');
        $oldData->setWebhookData($webhookData);
        $oldData->setStoreId('1');
        $this->webhookDataRepository->save($oldData);

        // act
        StoreContext::doWithStore('1', [$this->service, 'disconnect']);

        // assert
        /** @var WebhookDataEntity $connectionSettings */
        $webhookDataEntity = $this->webhookDataRepository->select();

        self::assertEmpty($webhookDataEntity);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     * @throws Exception
     */
    public function testConnectionDataDeleted(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::parse('live'),
            new ConnectionData('s-pub-live-test', 's-priv-live-test'),
            new ConnectionData('s-pub-sandbox-test', 's-priv-sandbox-test')
        );
        $settings = new ConnectionSettingsEntity();
        $settings->setConnectionSettings($connectionSettings);
        $settings->setStoreId('1');
        $this->connectionSettingsRepository->save($settings);

        $oldData = new WebhookDataEntity();
        $webhookData = new WebhookData('https://test2.com', ['1', '2', '3'], ['2', '3', '3'], 'test');
        $oldData->setWebhookData($webhookData);
        $oldData->setStoreId('1');
        $this->webhookDataRepository->save($oldData);

        // act
        StoreContext::doWithStore('1', [$this->service, 'disconnect']);

        // assert
        /** @var WebhookDataEntity $connectionSettings */
        $connectionSettings = $this->connectionSettingsRepository->select();

        self::assertEmpty($connectionSettings);
    }
}
