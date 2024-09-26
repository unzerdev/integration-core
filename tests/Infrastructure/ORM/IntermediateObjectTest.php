<?php

namespace Unzer\Core\Tests\Infrastructure\ORM;

use Unzer\Core\Infrastructure\ORM\IntermediateObject;
use PHPUnit\Framework\TestCase;

/**
 * Class IntermediateObjectTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM
 */
class IntermediateObjectTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexes()
    {
        $object = new IntermediateObject();
        $object->setIndex1('test');
        $object->setIndexValue(3, 'test2');

        $this->assertEquals('test', $object->getIndexValue(1));
        $this->assertEquals('test', $object->getIndex1());

        $this->assertEquals('test2', $object->getIndexValue(3));
        $this->assertEquals('test2', $object->getIndex3());

        for ($i = 1; $i < 10; $i++) {
            $object->setIndexValue($i, 'test' . $i);
        }

        for ($i = 1; $i < 10; $i++) {
            $this->assertEquals('test' . $i, $object->getIndexValue($i));
        }
    }
}
