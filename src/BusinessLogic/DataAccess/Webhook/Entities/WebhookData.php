<?php

namespace Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities;

use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;
use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData as DomainWebhookData;

/**
 * Class WebhookData.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\Webkhook\Entities
 */
class WebhookData extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var DomainWebhookData
     */
    protected DomainWebhookData $webhookData;

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

        return new EntityConfiguration($indexMap, 'WebhookData');
    }

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];

        $webhookData = $data['webhookData'] ?? [];
        $this->webhookData = new DomainWebhookData(
            $webhookData['url'],
            $webhookData['ids'],
            $webhookData['events'],
            $webhookData['createdAt']
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['webhookData'] = [
            'url' => $this->webhookData->getUrl(),
            'ids' => $this->webhookData->getIds(),
            'events' => $this->webhookData->getEvents(),
            'createdAt' => $this->webhookData->getCreateAt()
        ];

        return $data;
    }

    /**
     * @return DomainWebhookData
     */
    public function getWebhookData(): DomainWebhookData
    {
        return $this->webhookData;
    }

    /**
     * @param DomainWebhookData $webhookData
     *
     * @return void
     */
    public function setWebhookData(DomainWebhookData $webhookData): void
    {
        $this->webhookData = $webhookData;
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
