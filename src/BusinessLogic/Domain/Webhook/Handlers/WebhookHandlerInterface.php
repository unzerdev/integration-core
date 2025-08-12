<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Handlers;

use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use UnzerSDK\Resources\Payment;

interface WebhookHandlerInterface
{
    /**
     * @param Webhook $webhook
     * @param Payment $payment
     *
     * @return void
     */
    public function handleEvent(Webhook $webhook, Payment $payment): void;
}