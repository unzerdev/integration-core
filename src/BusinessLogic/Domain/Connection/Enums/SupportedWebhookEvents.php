<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Enums;

use UnzerSDK\Constants\WebhookEvents;

/**
 * Interface SupportedWebhookEvents.
 *
 * @package Unzer\Core\BusinessLogic\UnzerAPI\Connection\Enums
 */
interface SupportedWebhookEvents
{
    public const SUPPORTED_WEBHOOK_EVENTS = [
        WebhookEvents::AUTHORIZE,
        WebhookEvents::CHARGE,
        WebhookEvents::PAYMENT
    ];
}
