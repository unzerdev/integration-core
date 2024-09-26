<?php

namespace Unzer\Core\Tests\Infrastructure\ORM;

use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;

/**
 * Class MemoryGenericQueueItemRepositoryTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM
 */
class MemoryGenericQueueItemRepositoryTest extends AbstractGenericQueueItemRepositoryTest
{
    /**
     * @return string
     */
    public function getQueueItemEntityRepositoryClass(): string
    {
        return MemoryQueueItemRepository::getClassName();
    }

    /**
     * Cleans up all storage Services used by repositories
     */
    public function cleanUpStorage()
    {
        MemoryStorage::reset();
    }
}
