<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Models;

/**
 * Class ConnectionData.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Connection\Models
 */
class ConnectionData
{
    /**
     * @var string
     */
    private string $publicKey;

    /**
     * @var string
     */
    private string $privateKey;

    /**
     * @param string $publicKey
     * @param string $privateKey
     */
    public function __construct(string $publicKey, string $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * @return ConnectionData
     */
    public function clone(): ConnectionData
    {
        return clone $this;
    }
}
