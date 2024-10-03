<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\Webhook;

/**
 * Interface WebhookUrlServiceInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\Webhook
 */
interface WebhookUrlServiceInterface
{
    /**
     * @return string
     */
    public function getWebhookUrl(): string;
}
