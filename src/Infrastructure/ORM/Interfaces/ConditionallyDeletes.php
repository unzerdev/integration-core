<?php

namespace Unzer\Core\Infrastructure\ORM\Interfaces;

use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Interface ConditionallyDeletes.
 *
 * @package Unzer\Core\Infrastructure\ORM\Interfaces
 */
interface ConditionallyDeletes
{
    /**
     * @param QueryFilter|null $queryFilter
     *
     * @return mixed
     */
    public function deleteWhere(QueryFilter $queryFilter = null);
}
