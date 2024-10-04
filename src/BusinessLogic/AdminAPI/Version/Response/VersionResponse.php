<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Version\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Version\Models\Version;

/**
 * Class VersionResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Version\Response
 */
class VersionResponse extends Response
{
    /**
     * @var Version
     */
    private Version $version;

    /**
     * @param Version $version
     */
    public function __construct(Version $version)
    {
        $this->version = $version;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'installed' => $this->version->getInstalled(),
            'latest' => $this->version->getLatest()
        ];
    }
}
