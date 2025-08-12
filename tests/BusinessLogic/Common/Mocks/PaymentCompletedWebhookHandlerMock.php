<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Webhook\Handlers\WebhookHandlerInterface;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use UnzerSDK\Resources\Payment;

class PaymentCompletedWebhookHandlerMock implements WebhookHandlerInterface
{

    private array $callHistory = [];

    /**
     * @inheritDoc
     */
    public function handleEvent(Webhook $webhook, Payment $payment): void
    {
        $this->callHistory['handleEvent'][] = ['webhook' => $webhook, 'payment' => $payment];
    }

    public function getCallHistory($call)
    {
        return $this->callHistory[$call];
    }
}