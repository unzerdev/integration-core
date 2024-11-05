<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Models;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;

/**
 * Class WebhookSettings.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Webhook\Models
 */
class WebhookSettings
{
    /** @var Mode */
    private Mode $mode;

    /** @var ?WebhookData */
    private ?WebhookData $liveWebhookData;

    /** @var ?WebhookData */
    private ?WebhookData $sandboxWebhookData;

    /**
     * @param Mode $mode
     * @param ?WebhookData $liveWebhookData
     * @param ?WebhookData $sandboxWebhookData
     */
    public function __construct(
        Mode $mode,
        ?WebhookData $liveWebhookData = null,
        ?WebhookData $sandboxWebhookData = null
    ) {
        $this->mode = $mode;
        $this->liveWebhookData = $liveWebhookData;
        $this->sandboxWebhookData = $sandboxWebhookData;
    }

    /**
     * @return Mode
     */
    public function getMode(): Mode
    {
        return $this->mode;
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
     * @return WebhookData|null
     */
    public function getLiveWebhookData(): ?WebhookData
    {
        return $this->liveWebhookData;
    }

    /**
     * @param WebhookData|null $liveWebhookData
     *
     * @return void
     */
    public function setLiveWebhookData(?WebhookData $liveWebhookData): void
    {
        $this->liveWebhookData = $liveWebhookData;
    }

    /**
     * @return WebhookData|null
     */
    public function getSandboxWebhookData(): ?WebhookData
    {
        return $this->sandboxWebhookData;
    }

    /**
     * @param WebhookData|null $sandboxWebhookData
     *
     * @return void
     */
    public function setSandboxWebhookData(?WebhookData $sandboxWebhookData): void
    {
        $this->sandboxWebhookData = $sandboxWebhookData;
    }
}
