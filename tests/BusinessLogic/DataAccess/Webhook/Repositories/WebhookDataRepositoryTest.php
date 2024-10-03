<?php

namespace BusinessLogic\DataAccess\Webhook\Repositories;

use Exception;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookData as WebhookDataEntity;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class WebhookDataRepositoryTest.
 *
 * @package BusinessLogic\DataAccess\Webhook\Repositories
 */
class WebhookDataRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private RepositoryInterface $repository;

    /** @var WebhookDataRepositoryInterface */
    private $webhookDataRepository;

    /**
     * @throws RepositoryNotRegisteredException
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(WebhookDataEntity::getClassName());
        $this->webhookDataRepository = TestServiceRegister::getService(WebhookDataRepositoryInterface::class);
    }

    /**
     * @throws Exception
     */
    public function testGetDataNoData(): void
    {
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->webhookDataRepository, 'getWebhookData']
        );

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetData(): void
    {
        // arrange
        $settings = new WebhookData('test.com', ['1', '2'], ['test', 'test'], 'test');

        StoreContext::doWithStore('1',
            [$this->webhookDataRepository, 'setWebhookData'], [$settings]);
        // act
        $result = StoreContext::doWithStore('1',
            [$this->webhookDataRepository, 'getWebhookData']);

        // assert
        self::assertEquals($settings, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsSetForDifferentStore(): void
    {
        // arrange
        $settings = new WebhookData('test.com', ['1', '2'], ['test', 'test'], 'test');
        $settingsEntity = new WebhookDataEntity();
        $settingsEntity->setWebhookData($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('2',
            [$this->webhookDataRepository, 'getWebhookData']);

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
        $settings = new WebhookData('test.com', ['1', '2'], ['test', 'test'], 'test');

        // act
        StoreContext::doWithStore('1',
            [$this->webhookDataRepository, 'setWebhookData'],
            [$settings]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($settings, $savedEntity[0]->getWebhookData());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettingsAlreadyExists(): void
    {
        // arrange
        $settings = new WebhookData('test.com', ['1', '2'], ['test', 'test'], 'test');
        $settingsEntity = new WebhookDataEntity();
        $settingsEntity->setWebhookData($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = new WebhookData('test2.com', ['12', '23'], ['test2', 'test2'], 'test2');

        // act
        StoreContext::doWithStore('1',
            [$this->webhookDataRepository, 'setWebhookData'],
            [$newSettings]
        );

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($newSettings, $savedEntity->getWebhookData());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetSettingsAlreadyExistsForOtherStore()
    {
        // arrange
        $settings = new WebhookData('test.com', ['1', '2'], ['test', 'test'], 'test');
        $settingsEntity = new WebhookDataEntity();
        $settingsEntity->setWebhookData($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = new WebhookData('test2.com', ['12', '23'], ['test2', 'test2'], 'test2');
        // act
        StoreContext::doWithStore('2', [$this->webhookDataRepository, 'setWebhookData'],
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
        $settings = new WebhookData('test.com', ['1', '2'], ['test', 'test'], 'test');
        $settingsEntity = new WebhookDataEntity();
        $settingsEntity->setWebhookData($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        StoreContext::doWithStore('1', [$this->webhookDataRepository, 'deleteWebhookData']);

        // assert
        $webhookData = $this->repository->select();
        self::assertEmpty($webhookData);
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
        StoreContext::doWithStore('1', [$this->webhookDataRepository, 'deleteWebhookData']);

        // assert
        $webhookData = $this->repository->select();
        self::assertEmpty($webhookData);
    }
}
