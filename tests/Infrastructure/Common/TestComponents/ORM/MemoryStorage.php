<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM;

use Unzer\Core\Infrastructure\ORM\Entity;

/**
 * Class MemoryStorage.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM
 */
class MemoryStorage
{
    /**
     * @var int
     */
    private static int $incrementId = 1;

    /**
     * @var Entity[]
     */
    public static array $storage = [];

    /**
     * @return int
     */
    public static function generateId(): int
    {
        return static::$incrementId++;
    }

    /**
     * Empties storage.
     */
    public static function reset()
    {
        static::$incrementId = 1;
        static::$storage = [];
    }
}
