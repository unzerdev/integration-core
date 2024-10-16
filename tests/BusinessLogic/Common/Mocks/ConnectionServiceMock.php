<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;

/**
 * Class ConnectionServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class ConnectionServiceMock extends ConnectionService
{
    /**
     * @var ConnectionSettings|null
     */
    private ?ConnectionSettings $connectionSettings = null;

    /**
     * @var WebhookData|null
     */
    private ?WebhookData $webhookData = null;

    /**
     * @var bool
     */
    private bool $isLoggedIn = false;

    /**
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     */
    public function initializeConnection(ConnectionSettings $connectionSettings): void
    {
    }

    /**
     * @return ConnectionSettings|null
     */
    public function getConnectionSettings(): ?ConnectionSettings
    {
        return $this->connectionSettings;
    }

    /**
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     */
    public function setConnectionSettings(ConnectionSettings $connectionSettings): void
    {
        $this->connectionSettings = $connectionSettings;
    }

    /**
     * @return void
     */
    public function reRegisterWebhooks(): ?WebhookData
    {
        return $this->webhookData;
    }

    /**
     * @return WebhookData|null
     */
    public function getWebhookData(): ?WebhookData
    {
        return $this->webhookData;
    }

    /**
     * @param WebhookData $webhookData
     *
     * @return void
     */
    public function setWebhookData(WebhookData $webhookData): void
    {
        $this->webhookData = $webhookData;
    }

    /**
     * Change connection mode
     *
     * @param Mode $mode
     *
     * @return void
     */

    public function isLoggedIn(Mode $mode): bool
    {
        return $this->isLoggedIn;
    }

    /**
     * @param bool $isLoggedIn
     *
     * @return void
     */
    public function setLoggedIn(bool $isLoggedIn): void
    {
        $this->isLoggedIn = $isLoggedIn;
    }
}
