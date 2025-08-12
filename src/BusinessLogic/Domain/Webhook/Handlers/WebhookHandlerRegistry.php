<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Handlers;

use Unzer\Core\BusinessLogic\Domain\Payments\Common\Processors\RequestProcessor;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\Singleton;

class WebhookHandlerRegistry extends Singleton
{

    /**
     * Map of webhook event specific registered processors
     *
     * @var array<string, class-string<WebhookHandlerInterface>>
     */
    protected array $webhookEventHandlers = [];


    /**
     * Registers payment method specific processor that can be applied only for specified payment method type
     *
     * @param string $webhookEvent
     * @param class-string<WebhookHandlerInterface> $handlerClass
     * @return void
     */
    public static function registerWebhookHandler(string $webhookEvent, string $handlerClass): void
    {
        static::getInstance()->webhookEventHandlers[$webhookEvent] = $handlerClass;
    }

    /**
     * Gets all applicable payment request processors for a given payment method type
     *
     * @param string $event
     * @return WebhookHandlerInterface Applicable processors (includes both global and type-specific)
     */
    public static function getHandler(string $event): WebhookHandlerInterface
    {
        return static::getInstance()->get($event);
    }

    /**
     * @param string $event
     * @return WebhookHandlerInterface
     */
    protected function get(string $event): WebhookHandlerInterface
    {
        $class = array_key_exists($event, $this->webhookEventHandlers) ? $this->webhookEventHandlers[$event] : null;
        if ($class !== null) {
            return ServiceRegister::getService($class);
        }

        return ServiceRegister::getService(DefaultWebhookHandler::class);
    }
}
