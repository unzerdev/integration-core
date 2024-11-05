<?php

namespace Unzer\Core\Tests\BusinessLogic\UnzerAPI;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookSettingsRepositoryInterface;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\ConnectionServiceMock;
use Unzer\Core\Tests\BusinessLogic\Common\Mocks\UnzerFactoryMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use UnzerSDK\Unzer;

/**
 * Class UnzerFactoryTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\UnzerAPI
 */
class UnzerFactoryTest extends BaseTestCase
{
    /**
     * @var ?ConnectionServiceMock
     */
    private ?ConnectionServiceMock $connectionServiceMock = null;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connectionServiceMock = new ConnectionServiceMock(
            new UnzerFactoryMock(),
            TestServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
            TestServiceRegister::getService(WebhookSettingsRepositoryInterface::class),
            TestServiceRegister::getService(EncryptorInterface::class),
            TestServiceRegister::getService(WebhookUrlServiceInterface::class)
        );

        TestServiceRegister::registerService(
            ConnectionService::class, function () {
            return $this->connectionServiceMock;
        });
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     */
    public function testMakeUnzerWithConnectionSettings(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::live(),
            new ConnectionData('s-pub-live-test', 's-priv-live-test')
        );
        $expectedUnzer = new Unzer($connectionSettings->getLiveConnectionData()->getPrivateKey());

        // act
        $unzer = (new UnzerFactory())->makeUnzerAPI($connectionSettings);
        // assert
        self::assertEquals($expectedUnzer, $unzer);
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     */
    public function testMakeUnzerWithConnectionSettingsUnzerExists(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            Mode::live(),
            new ConnectionData('s-pub-live-test', 's-priv-live-test')
        );
        $expectedUnzer = new Unzer($connectionSettings->getLiveConnectionData()->getPrivateKey());

        // act
        $unzer1 = (new UnzerFactory())->makeUnzerAPI($connectionSettings);
        $unzer2 = (new UnzerFactory())->makeUnzerAPI($connectionSettings);
        // assert
        self::assertEquals($expectedUnzer, $unzer1);
        self::assertEquals($expectedUnzer, $unzer2);
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     */
    public function testMakeUnzerNoConnectionSettings(): void
    {
        // arrange
        $this->expectException(ConnectionSettingsNotFoundException::class);

        // act
        (new UnzerFactory())->makeUnzerAPI();
        // assert
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     */
    public function testMakeUnzerFromConnectionService(): void
    {
        // arrange
        $this->connectionServiceMock->setConnectionSettings(
            new ConnectionSettings(
                Mode::live(),
                new ConnectionData('s-pub-live-test', 's-priv-live-test')
            )
        );
        $expectedUnzer = new Unzer('s-priv-live-test');

        // act
        $unzer = (new UnzerFactory())->makeUnzerAPI();
        // assert
        self::assertEquals($expectedUnzer, $unzer);
    }
}
