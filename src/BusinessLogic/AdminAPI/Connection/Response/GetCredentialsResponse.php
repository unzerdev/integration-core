<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings;

/**
 * Class GetCredentialsResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Connection\Response
 */
class GetCredentialsResponse extends Response
{
    /**
     * @var ConnectionSettings|null
     */
    private ?ConnectionSettings $connectionSettings;

    /**
     * @var WebhookSettings|null
     */
    private ?WebhookSettings $webhookSettings;

    /**
     * @param ?ConnectionSettings $connectionSettings
     * @param ?WebhookSettings $webhookData
     */
    public function __construct(?ConnectionSettings $connectionSettings = null, ?WebhookSettings $webhookData = null)
    {
        $this->connectionSettings = $connectionSettings;
        $this->webhookSettings = $webhookData;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $returnArray = [];

        if(!$this->connectionSettings || !$this->webhookSettings) {
            return $returnArray;
        }

        if ($this->connectionSettings->getLiveConnectionData()) {
            $returnArray['live']['connectionData'] = [
                'privateKey' => $this->connectionSettings->getLiveConnectionData()->getPrivateKey(),
                'publicKey' => $this->connectionSettings->getLiveConnectionData()->getPublicKey()
            ];


            if ($this->webhookSettings->getLiveWebhookData()) {
                $returnArray['live']['webhookData'] = [
                    'registrationDate' => $this->webhookSettings->getLiveWebhookData()->getCreateAt(),
                    'webhookID' => implode(', ', $this->webhookSettings->getLiveWebhookData()->getIds()),
                    'events' => implode(', ', $this->webhookSettings->getLiveWebhookData()->getEvents()),
                    'webhookUrl' => $this->webhookSettings->getLiveWebhookData()->getUrl(),
                ];
            }
        }

        if ($this->connectionSettings->getSandboxConnectionData()) {
            $returnArray['sandbox']['connectionData'] = [
                'privateKey' => $this->connectionSettings->getSandboxConnectionData()->getPrivateKey(),
                'publicKey' => $this->connectionSettings->getSandboxConnectionData()->getPublicKey()
            ];


            if ($this->webhookSettings->getSandboxWebhookData()) {
                $returnArray['sandbox']['webhookData'] = [
                    'registrationDate' => $this->webhookSettings->getSandboxWebhookData()->getCreateAt(),
                    'webhookID' => implode(', ', $this->webhookSettings->getSandboxWebhookData()->getIds()),
                    'events' => implode(', ', $this->webhookSettings->getSandboxWebhookData()->getEvents()),
                    'webhookUrl' => $this->webhookSettings->getSandboxWebhookData()->getUrl(),
                ];
            }
        }

        return $returnArray;
    }
}
