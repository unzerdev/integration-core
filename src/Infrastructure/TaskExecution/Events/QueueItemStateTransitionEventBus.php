<?php

namespace Unzer\Core\Infrastructure\TaskExecution\Events;

use Unzer\Core\Infrastructure\Utility\Events\EventBus;

/**
 * Class QueueItemStateTransitionEventBus
 *
 * @package Unzer\Core\Infrastructure\TaskExecution\Events
 */
class QueueItemStateTransitionEventBus extends EventBus
{
    const CLASS_NAME = __CLASS__;

    protected static $instance;
}
