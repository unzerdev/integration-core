<?php

namespace BusinessLogic\AdminAPI\State;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\State\Request\StateRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookDataRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\ConnectionServiceMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class StateApiTest.
 *
 * @package BusinessLogic\AdminAPI\State
 */
class StateControllerTest extends BaseTestCase
{
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

        $this->connectionService = new ConnectionServiceMock(
            TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
            TestServiceRegister::getService(WebhookDataRepositoryInterface::class),
            TestServiceRegister::getService(EncryptorInterface::class),
            TestServiceRegister::getService(WebhookUrlServiceInterface::class)
        );

        TestServiceRegister::registerService(
            ConnectionService::class, function () {
            return $this->connectionService;
        });
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testStateSuccessWithLiveCredentials(): void
    {
        //Arrange
        $request = new StateRequest(Mode::LIVE);
        // Act
        $loginStatus = AdminAPI::get()->state('store1')->isLoggedIn($request);

        // Assert
        self::assertTrue($loginStatus->isSuccessful());
    }


    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testStateSuccessWithSandboxCredentials(): void
    {
        //Arrange
        $request = new StateRequest(Mode::SANDBOX);
        // Act
        $loginStatus = AdminAPI::get()->state('store1')->isLoggedIn($request);

        // Assert
        self::assertTrue($loginStatus->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testStateResponseFalse() : void
    {
        //Arrange
        $this->connectionService->setLoggedIn(false);

        $response = [
            'loggedIn' => false
        ];
        $request = new StateRequest(Mode::SANDBOX);

        // Act
        $loginStatus = AdminAPI::get()->state('store1')->isLoggedIn($request);

        // Assert
        self::assertEquals($loginStatus->toArray(), $response);
    }

    /**
     * @return void
     *
     * @throws InvalidModeException
     */
    public function testStateResponseTrue() : void
    {
        //Arrange
        $this->connectionService->setLoggedIn(true);

        $response = [
            'loggedIn' => true
        ];
        $request = new StateRequest(Mode::SANDBOX);

        // Act
        $loginStatus = AdminAPI::get()->state('store1')->isLoggedIn($request);

        // Assert
        self::assertEquals($loginStatus->toArray(), $response);
    }
}