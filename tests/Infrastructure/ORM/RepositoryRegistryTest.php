<?php

namespace Unzer\Core\Tests\Infrastructure\ORM;

use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Unzer\Core\Infrastructure\ORM\RepositoryRegistry;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use PHPUnit\Framework\TestCase;

/***
 * Class RepositoryRegistryTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM
 */
class RepositoryRegistryTest extends TestCase
{
    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    public function testRegisterRepository()
    {
        RepositoryRegistry::registerRepository('test', MemoryRepository::getClassName());

        $repository = RepositoryRegistry::getRepository('test');
        $this->assertInstanceOf(MemoryRepository::getClassName(), $repository);
    }

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function testRegisterRepositoryWrongRepoClass()
    {
        $this->expectException(RepositoryClassException::class);

        RepositoryRegistry::registerRepository('test', '\PHPUnit\Framework\TestCase');
    }

    /**
     * @return void
     *
     * @throws RepositoryNotRegisteredException
     */
    public function testRegisterRepositoryNotRegistered()
    {
        $this->expectException(RepositoryNotRegisteredException::class);

        RepositoryRegistry::getRepository('test2');
    }

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function testIsRegistered()
    {
        RepositoryRegistry::registerRepository('test', MemoryRepository::getClassName());
        $this->assertTrue(RepositoryRegistry::isRegistered('test'));
        $this->assertFalse(RepositoryRegistry::isRegistered('test2'));
    }
}
