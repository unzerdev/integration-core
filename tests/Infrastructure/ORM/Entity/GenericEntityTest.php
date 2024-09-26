<?php

namespace Unzer\Core\Tests\Infrastructure\ORM\Entity;

use InvalidArgumentException;
use Unzer\Core\Infrastructure\ORM\Configuration\Index;
use Unzer\Core\Infrastructure\ORM\Entity;
use PHPUnit\Framework\TestCase;

/**
 * Class GenericEntityTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM\Entity
 */
abstract class GenericEntityTest extends TestCase
{
    /** @var array|string[]  */
    public static array $ALLOWED_INDEX_TYPES = [
        'integer',
        'double',
        'dateTime',
        'string',
        'array',
        'boolean'
    ];

    /**
     * Returns entity full class name
     *
     * @return string
     */
    abstract public function getEntityClass(): string;

    /**
     * @return mixed
     */
    public function testEntityClass()
    {
        $entityClass = $this->getEntityClass();
        $entity = new $entityClass();

        $this->assertInstanceOf(Entity::getClassName(), $entity);

        return $entity;
    }

    /**
     * @depends testEntityClass
     *
     * @param Entity $entity
     */
    public function testEntityConfiguration(Entity $entity)
    {
        $config = $entity->getConfig();

        $type = $config->getType();
        $this->assertNotEmpty($type);
        $this->assertIsString($type);

        $indexMap = $config->getIndexMap();
        $this->assertInstanceOf("Unzer\Core\Infrastructure\ORM\Configuration\IndexMap", $indexMap);

        foreach ($indexMap->getIndexes() as $key => $item) {
            $this->assertNotEmpty($item, "Index configuration for $key must not be empty.");
            $this->assertInstanceOf("Unzer\Core\Infrastructure\ORM\Configuration\Index", $item);

            $this->assertContains(
                $item->getType(),
                self::$ALLOWED_INDEX_TYPES,
                "Index type '{$item->getType()}' for field $key is not supported."
            );
        }
    }

    /**
     * @return void
     */
    public function testInvalidIndexType()
    {
        $this->expectException(InvalidArgumentException::class);

        new Index('type', 'name');
    }
}
