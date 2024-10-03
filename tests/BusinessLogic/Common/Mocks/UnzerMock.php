<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use UnzerSDK\Resources\Keypair;
use UnzerSDK\Resources\Webhook;
use UnzerSDK\Unzer;

/**
 * Class UnzerMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class UnzerMock extends Unzer
{
    /**
     * @var Keypair|null
     */
    private ?Keypair $keypair = null;

    /**
     * @var Webhook[]
     */
    private array $webhooks = [];

    /**
     * @param Keypair $keypair
     *
     * @return void
     */
    public function setKeypair(Keypair $keypair): void
    {
        $this->keypair = $keypair;
    }

    /**
     * @param bool $detailed
     *
     * @return Keypair
     */
    public function fetchKeypair(bool $detailed = false): Keypair
    {
        return $this->keypair;
    }

    /**
     * @param string $url
     * @param array $events
     *
     * @return array
     */
    public function registerMultipleWebhooks(string $url, array $events): array
    {
        return $this->webhooks;
    }

    /**
     * @return void
     */
    public function deleteAllWebhooks(): void
    {
    }

    /**
     * @return array
     */
    public function fetchAllWebhooks(): array
    {
        return $this->webhooks;
    }

    /**
     * @param array $webhooks
     *
     * @return void
     */
    public function setWebhooks(array $webhooks): void
    {
        $this->webhooks = $webhooks;
    }
}
