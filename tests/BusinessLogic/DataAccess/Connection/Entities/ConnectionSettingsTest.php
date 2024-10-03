<?php

namespace Unzer\Core\Tests\BusinessLogic\DataAccess\Connection\Entities;

use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Unzer\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class ConnectionSettingsTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\DataAccess\Connection\Entities
 */
class ConnectionSettingsTest extends GenericEntityTest
{
    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return ConnectionSettings::getClassName();
    }
}
