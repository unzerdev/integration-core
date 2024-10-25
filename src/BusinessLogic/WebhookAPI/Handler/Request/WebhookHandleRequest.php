<?php

namespace Unzer\Core\BusinessLogic\WebhookAPI\Handler\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;
use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

/**
 * Class WebhookValidationRequest.
 *
 * @package Unzer\Core\BusinessLogic\WebhookAPI\Validation\Request
 */
class WebhookHandleRequest extends Request
{
    /**
     * @var string $payload
     */
    private string $payload;

    /**
     * @param string $payload
     */
    public function __construct(string $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return Webhook
     */
    public function toDomainModel(): Webhook
    {
        return Webhook::fromPayload($this->payload);
    }
}
