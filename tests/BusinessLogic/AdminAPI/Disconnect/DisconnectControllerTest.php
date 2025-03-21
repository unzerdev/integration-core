<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\Disconnect;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Interfaces\PaymentMethodConfigRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Repositories\PaymentPageSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Interfaces\PaymentStatusMapRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Interfaces\TransactionHistoryRepositoryInterface;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\DisconnectServiceMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class DisconnectControllerTest.
 *
 * @package BusinessLogic\AdminAPI\Disconnect
 */
class DisconnectControllerTest extends BaseTestCase
{
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
     */
    public function testDisconnectSuccess(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->disconnect('1')->disconnect();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws UnzerApiException
     */
    public function testDisconnectToArray(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->disconnect('1')->disconnect();

        // Assert
        self::assertEquals([], $response->toArray());
    }
}
