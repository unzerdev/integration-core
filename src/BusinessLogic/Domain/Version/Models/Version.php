<?php

namespace Unzer\Core\BusinessLogic\Domain\Version\Models;

/**
 * Class Version.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Version\Models
 */
class Version
{
    /**
     * @var string
     */
    private string $installed;
    /**
     * @var string
     */
    private string $latest;

    /**
     * @param string $installed
     * @param string $latest
     */
    public function __construct(string $installed, string $latest = '')
    {
        $this->installed = $installed;
        $this->latest = $latest;
    }

    /**
     * @return string
     */
    public function getInstalled(): string
    {
        return $this->installed;
    }

    /**
     * @return string
     */
    public function getLatest(): string
    {
        return $this->latest;
    }
}
