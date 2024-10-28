<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Unzer\Core\BusinessLogic\Domain\Webhook\Services\WebhookService;

/**
 * Class WebhookServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class WebhookServiceMock extends WebhookService
{
    /**
     * @param Webhook $webhook
     *
     * @return void
     */
    public function handle(Webhook $webhook): void
    {
    }
}
