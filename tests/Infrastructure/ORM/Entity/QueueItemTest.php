<?php

namespace Unzer\Core\Tests\Infrastructure\ORM\Entity;

use Unzer\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM\Entity
 */
class QueueItemTest extends GenericEntityTest
{
    /**
     * Returns entity full class name
     *
     * @return string
     */
    public function getEntityClass(): string
    {
        return QueueItem::getClassName();
    }
}
