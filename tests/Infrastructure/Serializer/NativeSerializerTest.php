<?php

namespace Unzer\Core\Tests\Infrastructure\Serializer;

use Unzer\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use Unzer\Core\Infrastructure\Serializer\Serializer;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class NativeSerializerTest
 *
 * @package Unzer\Core\Tests\Infrastructure\Serializer
 */
class NativeSerializerTest extends TestCase
{
    public function setUp(): void
    {
        TestServiceRegister::registerService(
            Serializer::CLASS_NAME,
            function () {
                return new NativeSerializer();
            }
        );
    }

    public function testNativeSerialization()
    {
        $task = new FooTask('test_1', 'test_2');
        /** @var FooTask $serialized */
        $serialized = Serializer::unserialize(Serializer::serialize($task));

        $this->assertInstanceOf(get_class($task), $serialized);
        $this->assertEquals($task->getDependency1(), $serialized->getDependency1());
        $this->assertEquals($task->getDependency2(), $serialized->getDependency2());
        $this->assertEquals($task->getMethodCallCount('execute'), $task->getMethodCallCount('execute'));
    }
}
