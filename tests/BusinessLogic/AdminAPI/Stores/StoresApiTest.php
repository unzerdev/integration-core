<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\Stores;

use Exception;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Response\StoreOrderStatusesResponse;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Response\StoreResponse;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Response\StoresResponse;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;
use Unzer\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\ConnectionServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\StoreServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\StoreServiceMock as IntegrationMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class StoresApiTest.
 *
 * @package BusinessLogic\AdminAPI\Stores
 */
class StoresApiTest extends BaseTestCase
{
    /**
     * @var StoreServiceMock
     */
    private StoreServiceMock $storeService;

    /**
     * @var ConnectionServiceMock
     */
    private ConnectionServiceMock $connectionService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->storeService = new StoreServiceMock(
            new IntegrationMock(),
        );

        $this->connectionService = new ConnectionServiceMock(
            new UnzerFactoryMock(),
            TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
            TestServiceRegister::getService(WebhookDataRepositoryInterface::class),
            TestServiceRegister::getService(EncryptorInterface::class),
            TestServiceRegister::getService(WebhookUrlServiceInterface::class)
        );

        TestServiceRegister::registerService(
            ConnectionService::class, function () {
            return $this->connectionService;
        });

        TestServiceRegister::registerService(
            StoreService::class, function () {
            return $this->storeService;
        });
    }

    /**
     * @return void
     */
    public function testGetStoresSuccess(): void
    {
        // Arrange
        

        // Act
        $response = AdminAPI::get()->stores()->getStores();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testGetStoresResponse(): void
    {
        // Arrange

        $this->storeService->setMockStores([
                new Store('store1', 'store1'),
                new Store('store2', 'store2'),
                new Store('store3', 'store3')
            ]
        );

        // Act
        $response = AdminAPI::get()->stores()->getStores();

        // Assert
        self::assertEquals($response, $this->expectedStoresResponse());
    }

    /**
     * @return void
     */
    public function testGetStoresResponseToArray(): void
    {
        // Arrange

        $this->storeService->setMockStores([
                new Store('store1', 'store1'),
                new Store('store2', 'store2'),
                new Store('store3', 'store3')
            ]
        );

        // Act
        $response = AdminAPI::get()->stores()->getStores();

        // Assert
        self::assertEquals($response->toArray(), $this->expectedStoresResponse()->toArray());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetCurrentStore(): void
    {
        // Arrange

        $this->storeService->setMockCurrentStore(new Store('storeId', 'store1'));

        // Act
        $response = AdminAPI::get()->stores()->getCurrentStore();

        // Assert
        self::assertEquals([
            'storeId' => 'storeId',
            'storeName' => 'store1',
            'isLoggedIn' => false,
            'mode' => 'live',
            'publicKey' => ''
        ], $response->toArray());
    }

    /**
     * @return void
     */
    public function testGetStoreOrderStatuses(): void
    {
        // Arrange
        $this->storeService->setMockStoreOrderStatuses([
            new StoreOrderStatus('authorized', 'Authorized'),
            new StoreOrderStatus('paid', 'Paid'),
            new StoreOrderStatus('refunded', 'Refunded')
        ]);

        // Act
        $response = AdminAPI::get()->stores()->getStoreOrderStatuses();

        // Assert
        self::assertEquals($response->toArray(), $this->expectedStoreOrderStatusesResponse()->toArray());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetCurrentStoreFailBack(): void
    {
        // Arrange

        $this->storeService->setMockCurrentStore(null);

        // Act
        $response = AdminAPI::get()->stores()->getCurrentStore();

        // Assert
        self::assertEquals($response, $this->expectedFailBackResponse());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetCurrentStoreLiveConnectionData(): void
    {
        // Arrange
        $settings = new ConnectionSettings(Mode::live(), new ConnectionData('publicKey' , 'private'));
        $this->connectionService->setConnectionSettings($settings);
        $this->storeService->setMockCurrentStore(new Store('storeId', 'store1'));

        // Act
        $response = AdminAPI::get()->stores()->getCurrentStore();

        // Assert
        self::assertEquals([
            'storeId' => 'storeId',
            'storeName' => 'store1',
            'isLoggedIn' => true,
            'mode' => 'live',
            'publicKey' => 'publicKey',
        ], $response->toArray());
    }

    /**
     * @return StoresResponse
     */
    private function expectedStoresResponse(): StoresResponse
    {
        return new StoresResponse([
            new Store('store1', 'store1'),
            new Store('store2', 'store2'),
            new Store('store3', 'store3',)
        ]);
    }

    /**
     * @return StoreOrderStatusesResponse
     */
    private function expectedStoreOrderStatusesResponse(): StoreOrderStatusesResponse
    {
        return new StoreOrderStatusesResponse([
            new StoreOrderStatus('authorized', 'Authorized'),
            new StoreOrderStatus('paid', 'Paid'),
            new StoreOrderStatus('refunded', 'Refunded')
        ]);
    }

    /**
     * @return StoreResponse
     */
    private function expectedFailBackResponse(): StoreResponse
    {
        return new StoreResponse(new Store('failBack', 'failBack'));
    }
}
