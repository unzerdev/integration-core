<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Models;

/**
 * Class ConnectionSettings.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Connection\Models
 */
class ConnectionSettings
{
    /** @var Mode */
    private Mode $mode;

    /** @var ?ConnectionData */
    private ?ConnectionData $liveConnectionData;

    /** @var ?ConnectionData */
    private ?ConnectionData $sandboxConnectionData;

    /**
     * @param Mode $mode
     * @param ConnectionData|null $liveConnectionData
     * @param ConnectionData|null $sandboxConnectionData
     */
    public function __construct(
        Mode $mode,
        ?ConnectionData $liveConnectionData = null,
        ?ConnectionData $sandboxConnectionData = null
    ) {
        $this->mode = $mode;
        $this->liveConnectionData = $liveConnectionData;
        $this->sandboxConnectionData = $sandboxConnectionData;
    }

    /**
     * @return ConnectionData|null
     */
    public function getLiveConnectionData(): ?ConnectionData
    {
        return $this->liveConnectionData;
    }

    /**
     * @return ConnectionData|null
     */
    public function getSandboxConnectionData(): ?ConnectionData
    {
        return $this->sandboxConnectionData;
    }

    /**
     * @param ConnectionData|null $liveConnectionData
     *
     * @return void
     */
    public function setLiveConnectionData(?ConnectionData $liveConnectionData): void
    {
        $this->liveConnectionData = $liveConnectionData;
    }

    /**
     * @param ConnectionData|null $sandboxConnectionData
     *
     * @return void
     */
    public function setSandboxConnectionData(?ConnectionData $sandboxConnectionData): void
    {
        $this->sandboxConnectionData = $sandboxConnectionData;
    }

    /**
     * @param Mode $mode
     *
     * @return void
     */
    public function setMode(Mode $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return Mode
     */
    public function getMode(): Mode
    {
        return $this->mode;
    }

    /**
     * Gets active connection setting data based on a selected mode
     *
     * @return ConnectionData
     */
    public function getActiveConnectionData(): ConnectionData
    {
        return $this->getMode()->equal(Mode::live()) ?
            $this->getLiveConnectionData() :
            $this->getSandboxConnectionData();
    }
}
