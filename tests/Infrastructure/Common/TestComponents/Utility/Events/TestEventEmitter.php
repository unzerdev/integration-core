<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\Events;

use Unzer\Core\Infrastructure\Utility\Events\Event;
use Unzer\Core\Infrastructure\Utility\Events\EventEmitter;

/**
 * Class TestEventEmitter.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\Events
 */
class TestEventEmitter extends EventEmitter
{
    /**
     * Singleton instance of this class.
     *
     * @var ?TestEventEmitter
     */
    protected static ?TestEventEmitter $instance = null;

    /**
     * @return TestEventEmitter
     */
    public static function getInstance(): TestEventEmitter
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function fire(Event $event)
    {
        parent::fire($event);
    }
}
