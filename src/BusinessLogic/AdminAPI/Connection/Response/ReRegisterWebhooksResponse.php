<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;

/**
 * Class ReRegisterWebhooksResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Connection\Response
 */
class ReRegisterWebhooksResponse extends Response
{

    /**
     * @var WebhookData|null
     */
    private ?WebhookData $webhookData;

    /**
     * @param ?WebhookData $webhookData
     */
    public function __construct(?WebhookData $webhookData = null)
    {
        $this->webhookData = $webhookData;
    }
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $returnArray = [];

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
