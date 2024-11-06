<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings;

/**
 * Class ReRegisterWebhooksResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Connection\Response
 */
class ReRegisterWebhooksResponse extends Response
{
    /**
     * @var WebhookSettings|null
     */
    private ?WebhookSettings $webhookSettings;

    /**
     * @param ?WebhookSettings $webhookSettings
     */
    public function __construct(?WebhookSettings $webhookSettings = null)
    {
        $this->webhookSettings = $webhookSettings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $returnArray = [];
        if (!$this->webhookSettings) {
            return $returnArray;
        }

        if ($this->webhookSettings->getMode()->equal(Mode::live()) && $this->webhookSettings->getLiveWebhookData()) {
            $returnArray['live']['webhookData'] = [
                'registrationDate' => $this->webhookSettings->getLiveWebhookData()->getCreateAt(),
                'webhookID' => implode(', ', $this->webhookSettings->getLiveWebhookData()->getIds()),
                'events' => implode(', ', $this->webhookSettings->getLiveWebhookData()->getEvents()),
                'webhookUrl' => $this->webhookSettings->getLiveWebhookData()->getUrl()
            ];
        }

        if ($this->webhookSettings->getMode()->equal(Mode::sandbox()) && $this->webhookSettings->getSandboxWebhookData()) {
            $returnArray['sandbox']['webhookData'] = [
                'registrationDate' => $this->webhookSettings->getSandboxWebhookData()->getCreateAt(),
                'webhookID' => implode(', ', $this->webhookSettings->getSandboxWebhookData()->getIds()),
                'events' => implode(', ', $this->webhookSettings->getSandboxWebhookData()->getEvents()),
                'webhookUrl' => $this->webhookSettings->getSandboxWebhookData()->getUrl()
            ];
        }

        return $returnArray;
    }
}
