<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Interfaces;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;

/**
 * Interface UnzerConnectionServiceInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Connection\Interfaces
 */
interface UnzerConnectionServiceInterface
{
    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function validateConnectionData(ConnectionData $connectionData): void;

    /**
     * @param string $webhookUrl
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function registerWebhooks(string $webhookUrl, ConnectionData $connectionData): WebhookData;
}
