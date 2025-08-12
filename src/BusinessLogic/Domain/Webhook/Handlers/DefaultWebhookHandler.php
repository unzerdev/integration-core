<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Handlers;

use Unzer\Core\BusinessLogic\Domain\Webhook\Handlers\WebhookHandlerInterface;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use UnzerSDK\Resources\Payment;

class DefaultWebhookHandler implements WebhookHandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(Webhook $webhook, Payment $payment): void
    {
    }
}