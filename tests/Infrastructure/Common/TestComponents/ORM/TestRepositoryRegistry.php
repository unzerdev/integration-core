<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM;

use Unzer\Core\Infrastructure\ORM\RepositoryRegistry;

/**
 * Class TestRepositoryRegistry.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM
 */
class TestRepositoryRegistry extends RepositoryRegistry
{
    /**
     * @return void
     */
    public static function cleanUp()
    {
        static::$repositories = [];
        static::$instantiated = [];
    }
}
