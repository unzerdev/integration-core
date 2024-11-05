<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings;

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
     * @var WebhookSettings|null
     */
    private ?WebhookSettings $webhookSettings = null;

    /** @var array */
    private array $ids = [];

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
    public function reRegisterWebhooks(): ?WebhookSettings
    {
        return $this->getWebhookSettings();
    }

    /**
     * @return WebhookData|null
     */
    public function getWebhookSettings(): ?WebhookSettings
    {
        return $this->webhookSettings;
    }

    /**
     * @param WebhookSettings $webhookSettings
     *
     * @return void
     */
    public function setWebhookSettings(WebhookSettings $webhookSettings): void
    {
        $this->webhookSettings = $webhookSettings;
    }

    /**
     * @return string[]
     */
    public function getConnectedStoreIds(): array
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     *
     * @return void
     */
    public function setIds(array $ids): void
    {
        $this->ids = $ids;
    }
}
