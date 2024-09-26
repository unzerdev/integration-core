<?php

namespace Unzer\Core\Infrastructure\TaskExecution\TaskEvents;

use Unzer\Core\Infrastructure\Utility\Events\Event;

/**
 * Class TickEvent.
 *
 * @package Unzer\Core\Infrastructure\Scheduler
 */
class TickEvent extends Event
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
}
