<?php

namespace Unzer\Core\Infrastructure\ORM\Interfaces;

use Unzer\Core\Infrastructure\ORM\Entity;

/**
 * Interface MassInsert.
 *
 * @package Unzer\Core\Infrastructure\ORM\Interfaces
 */
interface MassInsert
{
    /**
     * Executes mass insert query for all provided entities
     *
     * @param Entity[] $entities
     *
     * @return void
     */
    public function massInsert(array $entities): void;
}
