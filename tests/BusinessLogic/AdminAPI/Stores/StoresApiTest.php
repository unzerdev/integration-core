<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\Stores;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Response\StoreResponse;
use Unzer\Core\BusinessLogic\AdminAPI\Stores\Response\StoresResponse;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
use Unzer\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\StoreServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\StoreServiceMock as IntegrationMock;
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
        $response = AdminAPI::get()->stores('1')->getStores();

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
        $response = AdminAPI::get()->stores('1')->getStores();

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
        $response = AdminAPI::get()->stores('1')->getStores();

        // Assert
        self::assertEquals($response->toArray(), $this->expectedStoresResponse()->toArray());
    }

    /**
     * @return void
     */
    public function testGetCurrentStore(): void
    {
        // Arrange

        $this->storeService->setMockCurrentStore(new Store('storeId', 'store1'));

        // Act
        $response = AdminAPI::get()->stores('1')->getCurrentStore();

        // Assert
        self::assertEquals([
            'storeId' => 'storeId',
            'storeName' => 'store1',
        ], $response->toArray());
    }

    /**
     * @return void
     */
    public function testGetCurrentStoreFailBack(): void
    {
        // Arrange

        $this->storeService->setMockCurrentStore(null);

        // Act
        $response = AdminAPI::get()->stores('1')->getCurrentStore();

        // Assert
        self::assertEquals($response, $this->expectedFailBackResponse());
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
     * @return StoreResponse
     */
    private function expectedFailBackResponse(): StoreResponse
    {
        return new StoreResponse(new Store('failBack', 'failBack'));
    }
}
