<?php

namespace Unzer\Core\Tests\Infrastructure\ORM\Entity;

use Unzer\Core\Infrastructure\TaskExecution\Process;

/**
 * Class ProcessTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM\Entity
 */
class ProcessTest extends GenericEntityTest
{
    /**
     * Returns entity full class name
     *
     * @return string
     */
    public function getEntityClass(): string
    {
        return Process::getClassName();
    }
}
