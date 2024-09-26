<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\Entity;

use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;

/**
 * Class FooEntity.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\Entity
 */
class FooEntity extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    public string $text = 'Test';
    public int $int = 123;
    public int $intNegative = -234;
    public $date;
    public bool $boolTrue = true;
    public bool $boolFalse = false;
    public float $double = 123.5;
    public float $doubleNegative = -678.75;
    public int $empty = 123;

    /**
     * Array of field names.
     *
     * @var array
     */
    protected array $fields = [
        'id',
        'text',
        'int',
        'intNegative',
        'date',
        'boolTrue',
        'boolFalse',
        'double',
        'doubleNegative',
        'empty'
    ];

    /**
     * Returns entity configuration object
     *
     * @return EntityConfiguration
     */
    public function getConfig(): EntityConfiguration
    {
        $map = new IndexMap();
        $map->addStringIndex('text');
        $map->addIntegerIndex('int');
        $map->addIntegerIndex('intNegative');
        $map->addDateTimeIndex('date');
        $map->addBooleanIndex('boolTrue');
        $map->addBooleanIndex('boolFalse');
        $map->addDoubleIndex('double');
        $map->addDoubleIndex('doubleNegative');
        $map->addDoubleIndex('empty');

        return new EntityConfiguration($map, 'TestEntity');
    }
}
