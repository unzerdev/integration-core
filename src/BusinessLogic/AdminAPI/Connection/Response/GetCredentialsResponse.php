<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;

/**
 * Class GetCredentialsResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Connection\Response
 */
class GetCredentialsResponse extends Response
{
    /**
     * @var ConnectionData|null
     */
    private ?ConnectionData $connectionData;

    /**
     * @var WebhookData|null
     */
    private ?WebhookData $webhookData;

    /**
     * @param ?ConnectionData $connectionData
     * @param ?WebhookData $webhookData
     */
    public function __construct(?ConnectionData $connectionData = null, ?WebhookData $webhookData = null)
    {
        $this->connectionData = $connectionData;
        $this->webhookData = $webhookData;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $returnArray = [];

        if ($this->connectionData) {
            $returnArray['connectionData'] = [
                'privateKey' => $this->connectionData->getPrivateKey(),
                'publicKey' => $this->connectionData->getPublicKey(),
            ];
        }

        if ($this->webhookData) {
            $returnArray['webhookData'] = [
                'registrationDate' => $this->webhookData->getCreateAt(),
                'webhookID' => implode(', ', $this->webhookData->getIds()),
                'events' => implode(', ', $this->webhookData->getEvents()),
                'webhookUrl' => $this->webhookData->getUrl(),
            ];
        }

        return $returnArray;
    }
}
