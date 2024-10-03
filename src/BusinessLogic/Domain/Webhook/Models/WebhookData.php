<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Models;

use Unzer\Core\Infrastructure\Utility\TimeProvider;
use UnzerSDK\Resources\Webhook;

/**
 * Class WebhookData.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Webhook\Models
 */
class WebhookData
{
    /**
     * @var string
     */
    private string $url;

    /**
     * @var array
     */
    private array $ids;

    /**
     * @var array
     */
    private array $events;

    /**
     * @var string
     */
    private string $createAt;

    /**
     * @param string $url
     * @param array $ids
     * @param array $events
     * @param string $createAt
     */
    public function __construct(string $url, array $ids, array $events, string $createAt)
    {
        $this->url = $url;
        $this->ids = $ids;
        $this->events = $events;
        $this->createAt = $createAt;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @return string
     */
    public function getCreateAt(): string
    {
        return $this->createAt;
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

    /**
     * @param array $events
     *
     * @return void
     */
    public function setEvents(array $events): void
    {
        $this->events = $events;
    }

    /**
     * @param Webhook[] $data
     *
     * @return self
     */
    public static function fromBatch(array $data): self
    {
        $ids = [];
        $events = [];
        $url = '';
        foreach ($data as $webhook) {
            $ids[] = $webhook->getId();
            $events[] = $webhook->getEvent();
            $url = $webhook->getUrl();
        }

        return new self(
            $url,
            $ids,
            $events,
            TimeProvider::getInstance()->getCurrentLocalTime()->format('F d, Y H:i')
        );
    }
}
