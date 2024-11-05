<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Repositories;

use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings;

/**
 * Interface WebhookSettingsRepositoryInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Webhook\Repositories
 */
interface WebhookSettingsRepositoryInterface
{
    /**
     * @return WebhookSettings|null
     */
    public function getWebhookSettings(): ?WebhookSettings;

    /**
     * Sets webhook settings.
     *
     * @param WebhookSettings $webhookData
     *
     * @return void
     */
    public function setWebhookSettings(WebhookSettings $webhookData): void;

    /**
     * @return void
     */
    public function deleteWebhookSettings(): void;
}
