<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;

class ReconnectRequest extends Request
{
    /**
     * @var string
     */
    private string $mode;

    /**
     * @var string
     */
    private string $publicKey;

    /**
     * @var string
     */
    private string $privateKey;

    /**
     * @var bool
     */
    private bool $deleteConfig;

    /**
     * @param string $mode
     * @param string $publicKey
     * @param string $privateKey
     * @param bool $deleteConfig
     */
    public function __construct(string $mode, string $publicKey, string $privateKey, bool $deleteConfig = false)
    {
        $this->mode = $mode;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->deleteConfig = $deleteConfig;
    }

    /**
     * @return ConnectionSettings
     *
     * @throws InvalidModeException
     */
    public function toDomainModel(): ConnectionSettings
    {
        $mode = Mode::parse($this->mode);

        $connectionData = new ConnectionData($this->publicKey, $this->privateKey);

        if ($mode->equal(Mode::live())) {
            return new ConnectionSettings($mode, $connectionData);
        }

        return new ConnectionSettings($mode, null, $connectionData);
    }

    /**
     * @return bool
     */
    public function isDeleteConfig(): bool
    {
        return $this->deleteConfig;
    }
}
