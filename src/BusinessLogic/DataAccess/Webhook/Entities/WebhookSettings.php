<?php

namespace Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;
use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookSettings as DomainWebhookSettings;

/**
 * Class WebhookSettings.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities
 */
class WebhookSettings extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var DomainWebhookSettings
     */
    protected DomainWebhookSettings $webhookSettings;

    /**
     * @var string
     */
    protected string $storeId;

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'WebhookSettings');
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidModeException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];

        $webhookSettings = $data['webhookSettings'] ?? [];
        $this->webhookSettings = new DomainWebhookSettings(
            Mode::parse($webhookSettings['mode']),
            !empty($webhookSettings['liveData']) ?
                new WebhookData(
                    $webhookSettings['liveData']['url'] ?? '',
                        $webhookSettings['liveData']['ids'] ?? '',
                        $webhookSettings['liveData']['events'] ?? '',
                        $webhookSettings['liveData']['createdAt'] ?? '',
                ) : null,
            !empty($webhookSettings['sandboxData']) ?
                new WebhookData(
                    $webhookSettings['sandboxData']['url'] ?? '',
                        $webhookSettings['sandboxData']['ids'] ?? '',
                        $webhookSettings['sandboxData']['events'] ?? '',
                        $webhookSettings['sandboxData']['createdAt'] ?? ''
                ) : null
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['webhookSettings'] = [
            'mode' => $this->webhookSettings->getMode()->getMode(),
            'liveData' => $this->webhookSettings->getLiveWebhookData() ? [
                'url' => $this->webhookSettings->getLiveWebhookData()->getUrl(),
                'ids' => $this->webhookSettings->getLiveWebhookData()->getIds(),
                'events' => $this->webhookSettings->getLiveWebhookData()->getEvents(),
                'createdAt' => $this->webhookSettings->getLiveWebhookData()->getCreateAt()
            ] : [],
            'sandboxData' => $this->webhookSettings->getSandboxWebhookData() ? [
                'url' => $this->webhookSettings->getSandboxWebhookData()->getUrl(),
                'ids' => $this->webhookSettings->getSandboxWebhookData()->getIds(),
                'events' => $this->webhookSettings->getSandboxWebhookData()->getEvents(),
                'createdAt' => $this->webhookSettings->getSandboxWebhookData()->getCreateAt()
            ] : [],
        ];

        return $data;
    }

    /**
     * @return DomainWebhookSettings
     */
    public function getWebhookSettings(): DomainWebhookSettings
    {
        return $this->webhookSettings;
    }

    /**
     * @param DomainWebhookSettings $webhookSettings
     *
     * @return void
     */
    public function setWebhookSettings(DomainWebhookSettings $webhookSettings): void
    {
        $this->webhookSettings = $webhookSettings;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     *
     * @return void
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }
}
