<?php

namespace Unzer\Core\Tests\Infrastructure\ORM;

use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use PHPUnit\Framework\TestCase;

/**
 * Class EntityConfigurationTest
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM
 */
class EntityConfigurationTest extends TestCase
{
    /**
     * @return void
     */
    public function testEntityConfiguration()
    {
        $map = new IndexMap();
        $type = 'test';
        $config = new EntityConfiguration($map, $type);

        $this->assertEquals($map, $config->getIndexMap());
        $this->assertEquals($type, $config->getType());
    }
}
