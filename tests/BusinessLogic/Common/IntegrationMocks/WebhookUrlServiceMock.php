<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;

/**
 * Class WebhookUrlServiceMock.
 *
 * @package BusinessLogic\Common\IntegrationMocks
 */
class WebhookUrlServiceMock implements WebhookUrlServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getWebhookUrl(): string
    {
        return 'https://test.com';
    }
}
