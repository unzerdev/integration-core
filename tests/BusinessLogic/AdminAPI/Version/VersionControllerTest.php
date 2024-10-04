<?php

namespace Unzer\Core\Tests\BusinessLogic\AdminAPI\Version;

use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\Domain\Integration\Versions\VersionService;
use Unzer\Core\BusinessLogic\Domain\Version\Models\Version;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks\VersionServiceMock as IntegrationMock;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class VersionControllerTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\AdminAPI\Version
 */
class VersionControllerTest extends BaseTestCase
{
    /**
     * @var IntegrationMock
     */
    private IntegrationMock $versionServiceMock;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->versionServiceMock = new IntegrationMock();

        TestServiceRegister::registerService(
            VersionService::class, function () {
            return $this->versionServiceMock;
        });
    }

    /**
     * @return void
     */
    public function testGetVersionSuccess(): void
    {
        // Arrange
        $this->versionServiceMock->setVersion(new Version('version1', 'version2'));

        // Act
        $response = AdminAPI::get()->version('1')->getVersion();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testGetVersionToArray(): void
    {
        // Arrange
        $this->versionServiceMock->setVersion(new Version('version1', 'version2'));

        // Act
        $response = AdminAPI::get()->version('1')->getVersion();

        // Assert
        self::assertEquals(['installed' => 'version1', 'latest' => 'version2'],$response->toArray());
    }
}
