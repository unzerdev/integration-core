<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Repositories;

use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;

/**
 * Interface WebhookDataRepositoryInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Webhook\Repositories
 */
interface WebhookDataRepositoryInterface
{
    /**
     * @return WebhookData|null
     */
    public function getWebhookData(): ?WebhookData;

    /**
     * Sets webhook data.
     *
     * @param WebhookData $webhookData
     *
     * @return void
     */
    public function setWebhookData(WebhookData $webhookData): void;

    /**
     * @return void
     */
    public function deleteWebhookData(): void;
}
