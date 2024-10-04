<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\Versions;

use Unzer\Core\BusinessLogic\Domain\Version\Models\Version;

/**
 * Interface VersionService.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\Versions
 */
interface VersionService
{
    /**
     *  Retrieves plugin current and latest version.
     *
     * @return Version
     */
    public function getVersion(): Version;
}
