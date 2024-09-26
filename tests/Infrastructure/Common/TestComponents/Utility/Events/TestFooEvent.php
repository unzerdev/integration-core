<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\Events;

use Unzer\Core\Infrastructure\Utility\Events\Event;

/**
 * Class TestFooEvent.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\Utility\Events
 */
class TestFooEvent extends Event
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
}
