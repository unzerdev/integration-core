<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents;

use Unzer\Core\Infrastructure\Http\AsyncSocketHttpClient;

/**
 * Class TestAsyncSocketHttpClient.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents
 */
class TestAsyncSocketHttpClient extends AsyncSocketHttpClient
{
    /**
     * @var array
     */
    public array $requestHistory = [];

    /**
     * @param $transferProtocol
     * @param $host
     * @param $port
     * @param $timeOut
     * @param $payload
     *
     * @return void
     */
    protected function executeRequest($transferProtocol, $host, $port, $timeOut, $payload)
    {
        $this->requestHistory[] = [
            'transferProtocol' => $transferProtocol,
            'host' => $host,
            'port' => $port,
            'timeout' => $timeOut,
            'payload' => $payload,
        ];
    }
}
