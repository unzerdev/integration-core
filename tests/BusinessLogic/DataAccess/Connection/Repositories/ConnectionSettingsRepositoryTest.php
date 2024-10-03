<?php

namespace Unzer\Core\Tests\BusinessLogic\Connection\Repositories;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings as ConnectionSettingsModel;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ConnectionSettingsRepositoryTest.
 *
 * @package BusinessLogic\DataAccess\Connection\Repositories
 */
class ConnectionSettingsRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private RepositoryInterface $repository;

    /** @var ConnectionSettingsRepositoryInterface */
    private $connectionSettingsRepository;

    /**
     * @throws RepositoryNotRegisteredException
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(ConnectionSettings::getClassName());
        $this->connectionSettingsRepository = TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->connectionSettingsRepository, 'getConnectionSettings']
        );

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettings(): void
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            Mode::parse('live'),
            new ConnectionData('publicKey', 'privateKey'),
            new ConnectionData('publicKey', 'privateKey')
        );

        StoreContext::doWithStore('1',
            [$this->connectionSettingsRepository, 'setConnectionSettings'], [$settings]);
        // act
        $result = StoreContext::doWithStore('1',
            [$this->connectionSettingsRepository, 'getConnectionSettings']);

        // assert
        self::assertEquals($settings, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsSetForDifferentStore(): void
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            Mode::parse('live'),
            new ConnectionData('publicKey', 'privateKey'),
            null
        );
        $settingsEntity = new ConnectionSettings();
        $settingsEntity->setConnectionSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('2',
            [$this->connectionSettingsRepository, 'getConnectionSettings']);

        // assert
        self::assertNull($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettings(): void
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            Mode::parse('live'),
            new ConnectionData('publicKey', 'privateKey'),
            new ConnectionData('publicKey', 'privateKey')
        );

        // act
        StoreContext::doWithStore('1',
            [$this->connectionSettingsRepository, 'setConnectionSettings'],
            [$settings]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($settings, $savedEntity[0]->getConnectionSettings());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettingsAlreadyExists(): void
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            Mode::parse('live'),
            new ConnectionData('publicKey', 'privateKey'),
            null
        );
        $settingsEntity = new ConnectionSettings();
        $settingsEntity->setConnectionSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = new ConnectionSettingsModel(
            Mode::parse('live'),
            new ConnectionData('publicKey', 'privateKey'),
            null
        );

        // act
        StoreContext::doWithStore('1',
            [$this->connectionSettingsRepository, 'setConnectionSettings'],
            [$newSettings]
        );

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($newSettings, $savedEntity->getConnectionSettings());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettingsAlreadyExistsForOtherStore()
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            Mode::parse('live'),
            new ConnectionData('publicKey', 'privateKey'),
            new ConnectionData('publicKey', 'privateKey')
        );
        $settingsEntity = new ConnectionSettings();
        $settingsEntity->setConnectionSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = new ConnectionSettingsModel(
            Mode::parse('live'),
            new ConnectionData('publicKey', 'privateKey'),
            new ConnectionData('publicKey', 'privateKey')
        );

        // act
        StoreContext::doWithStore('2', [$this->connectionSettingsRepository, 'setConnectionSettings'],
            [$newSettings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteWebhookData(): void
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            Mode::parse('live'),
            new ConnectionData('publicKey', 'privateKey'),
            new ConnectionData('publicKey', 'privateKey')
        );
        $settingsEntity = new ConnectionSettings();
        $settingsEntity->setConnectionSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        // act
        StoreContext::doWithStore('1', [$this->connectionSettingsRepository, 'deleteConnectionSettings']);

        // assert
        $connectionSettings = $this->repository->select();
        self::assertEmpty($connectionSettings);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteWebhookDataNoData(): void
    {
        // arrange

        // act
        StoreContext::doWithStore('1', [$this->connectionSettingsRepository, 'deleteConnectionSettings']);

        // assert
        $connectionSettings = $this->repository->select();
        self::assertEmpty($connectionSettings);
    }
}
