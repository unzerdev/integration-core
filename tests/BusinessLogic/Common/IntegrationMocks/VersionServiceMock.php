<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Integration\Versions\VersionService;
use Unzer\Core\BusinessLogic\Domain\Version\Models\Version;

/**
 * Class VersionService.
 *
 * @package BusinessLogic\Common\IntegrationMocks
 */
class VersionServiceMock implements VersionService
{
    /**
     * @var Version|null
     */
    private ?Version $version = null;

    /**
     * @inheritDoc
     */
    public function getVersion(): Version
    {
        return $this->version;
    }

    /**
     * @param Version $version
     *
     * @return void
     */
    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }
}
