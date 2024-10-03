<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;

/**
 * Class ConnectionRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Connection\Request
 */
class ConnectionRequest extends Request
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
     * @param string $mode
     * @param string $publicKey
     * @param string $privateKey
     */
    public function __construct(string $mode, string $publicKey, string $privateKey)
    {
        $this->mode = $mode;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
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
}
