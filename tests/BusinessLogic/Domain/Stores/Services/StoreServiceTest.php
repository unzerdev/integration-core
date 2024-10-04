<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\Stores\Services;

use Exception;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;
use Unzer\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\StoreServiceMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Unzer\Core\BusinessLogic\Domain\Integration\Store\StoreService as IntegrationStoreService;

/**
 * Class StoreServiceTest.
 *
 * @package BusinessLogic\Domain\Stores\Services
 */
class StoreServiceTest extends BaseTestCase
{
    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * @var IntegrationStoreService
     */
    private IntegrationStoreService $integrationStoreService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationStoreService = new StoreServiceMock();

        TestServiceRegister::registerService(
            IntegrationStoreService::class, function () {
            return $this->integrationStoreService;
        });

        $this->storeService = TestServiceRegister::getService(StoreService::class);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetStoresEmptyArray(): void
    {
        // arrange

        // act
        $stores = StoreContext::doWithStore('1', [$this->storeService, 'getStores']);

        // assert
        self::assertEmpty($stores);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetStores(): void
    {
        // arrange

        $this->integrationStoreService->setMockStores([
            new Store('1', 'Store1'),
            new Store('2', 'Store2'),
            new Store('3', 'Store3'),
        ]);

        // act
        $stores = StoreContext::doWithStore('1', [$this->storeService, 'getStores']);

        // assert
        self::assertCount(3, $stores);
        self::assertEquals('1', $stores[0]->getStoreId());
        self::assertEquals('Store1', $stores[0]->getStoreName());
        self::assertEquals('2', $stores[1]->getStoreId());
        self::assertEquals('Store2', $stores[1]->getStoreName());
        self::assertEquals('3', $stores[2]->getStoreId());
        self::assertEquals('Store3', $stores[2]->getStoreName());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetCurrentStoreWithCurrentStore(): void
    {
        // arrange
        $store = new Store('1', 'Store1');
        $this->integrationStoreService->setMockCurrentStore($store);

        // act
        $currentStore = StoreContext::doWithStore('1', [$this->storeService, 'getCurrentStore']);

        // assert
        self::assertEquals($store, $currentStore);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetCurrentStoreWithDefault(): void
    {
        // arrange
        $store = new Store('1', 'Store1');
        $this->integrationStoreService->setMockCurrentStore(null);
        $this->integrationStoreService->setMockDefaultStore($store);

        // act
        $currentStore = StoreContext::doWithStore('1', [$this->storeService, 'getCurrentStore']);

        // assert
        self::assertEquals($store, $currentStore);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetStoreOrderStatusesNoStatuses(): void
    {
        // arrange

        // act
        $statuses = StoreContext::doWithStore('1', [$this->storeService, 'getStoreOrderStatuses']);

        // assert
        self::assertEmpty($statuses);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetStoreOrderStatuses(): void
    {
        // arrange

        $this->integrationStoreService->setStoreOrderStatuses([
            new StoreOrderStatus('1', 'status1'),
            new StoreOrderStatus('2', 'status2'),
            new StoreOrderStatus('3', 'status3')
        ]);

        // act
        $statuses = StoreContext::doWithStore('1', [$this->storeService, 'getStoreOrderStatuses']);

        // assert
        self::assertCount(3, $statuses);
        self::assertEquals('1', $statuses[0]->getStatusId());
        self::assertEquals('status1', $statuses[0]->getStatusName());
        self::assertEquals('2', $statuses[1]->getStatusId());
        self::assertEquals('status2', $statuses[1]->getStatusName());
        self::assertEquals('3', $statuses[2]->getStatusId());
        self::assertEquals('status3', $statuses[2]->getStatusName());
    }
}
