<?php

namespace Unzer\Core\Tests\Infrastructure;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Unzer\Core\Infrastructure\Exceptions\ServiceNotRegisteredException;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestService;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestServiceInterface;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ServiceRegisterTest.
 *
 * @package Infrastructure
 */
class ServiceRegisterTest extends TestCase
{
    /**
     * Test simple registering the service and getting the instance back
     *
     * @throws InvalidArgumentException
     */
    public function testGetInstance()
    {
        $service = ServiceRegister::getInstance();

        $this->assertInstanceOf(
            '\Unzer\Core\Infrastructure\ServiceRegister',
            $service,
            'Failed to retrieve registered instance of interface.'
        );
    }

    /**
     * Test simple registering the service and getting the instance back
     *
     */
    public function testSimpleRegisterAndGet()
    {
        new TestServiceRegister(
            [
                TestServiceInterface::CLASS_NAME => function () {
                    return new TestService('first');
                },
            ]
        );

        $result = ServiceRegister::getService(TestServiceInterface::CLASS_NAME);

        $this->assertInstanceOf(
            TestServiceInterface::CLASS_NAME,
            $result,
            'Failed to retrieve registered instance of interface.'
        );
    }

    /**
     * Test simple registering the service via static call and getting the instance back
     */
    public function testStaticSimpleRegisterAndGet()
    {
        ServiceRegister::registerService(
            'test 2',
            function () {
                return new TestService('first');
            }
        );

        $result = ServiceRegister::getService(TestServiceInterface::CLASS_NAME);

        $this->assertInstanceOf(
            TestServiceInterface::CLASS_NAME,
            $result,
            'Failed to retrieve registered instance of interface.'
        );
    }

    /**
     * Test throwing exception when service is not registered.
     */
    public function testGettingServiceWhenItIsNotRegistered()
    {
        $this->expectException(ServiceNotRegisteredException::class);
        ServiceRegister::getService('SomeService');
    }

    /**
     * Test throwing exception when trying to register service with non callable delegate
     */
    public function testRegisteringServiceWhenDelegateIsNotCallable()
    {
        $this->expectException(InvalidArgumentException::class);
        new TestServiceRegister(
            [
                TestServiceInterface::CLASS_NAME => 'Some non callable string',
            ]
        );
    }
}
