<?php

namespace Unzer\Core\Tests\Infrastructure\ORM;

use DateTime;
use Unzer\Core\Infrastructure\ORM\Utility\IndexHelper;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\Entity\FooEntity;
use PHPUnit\Framework\TestCase;

/**
 * Class IndexHelperTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM
 */
class IndexHelperTest extends TestCase
{
    /**
     * @var FooEntity
     */
    protected $entity;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = new FooEntity();
    }

    /**
     * @return void
     */
    public function testMapFieldsToIndexes()
    {
        $expected = [
            'text' => 1,
            'int' => 2,
            'intNegative' => 3,
            'date' => 4,
            'boolTrue' => 5,
            'boolFalse' => 6,
            'double' => 7,
            'doubleNegative' => 8,
            'empty' => 9,
        ];

        $result = IndexHelper::mapFieldsToIndexes($this->entity);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testTransformFieldsToIndexes()
    {
        $date = DateTime::createFromFormat('Y-m-d', '2018-11-20');
        $this->entity->date = $date;
        $expected = [
            1 => 'Test',
            2 => '00000000123',
            3 => '-0000000234',
            4 => (string)$date->getTimestamp(),
            5 => '1',
            6 => '0',
            7 => '00000000123.50000',
            8 => '-0000000678.75000',
            9 => null,
        ];

        $result = IndexHelper::transformFieldsToIndexes($this->entity);
        $this->assertEquals($expected, $result);
    }
}
