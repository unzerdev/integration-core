<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;

/**
 * Class GetConnectionDataResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Connection\Response
 */
class GetConnectionDataResponse extends Response
{
    /**
     * @var ?ConnectionData
     */
    private ?ConnectionData $connectionData;

    /**
     * @param ?ConnectionData $connectionData
     */
    public function __construct(?ConnectionData $connectionData = null)
    {
        $this->connectionData = $connectionData;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->connectionData) {
            return [];
        }

        return [
            'publicKey' => $this->connectionData->getPublicKey(),
            'privateKey' => $this->connectionData->getPrivateKey(),
        ];
    }
}
