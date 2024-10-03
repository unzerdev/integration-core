<?php

namespace Unzer\Core\Tests\BusinessLogic\Common;

use Unzer\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

trait MockConditionalDelete
{
    /**
     * @param QueryFilter|null $queryFilter
     *
     * @return void
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     */
    public function deleteWhere(QueryFilter $queryFilter = null): void
    {
        // IMPORTANT NOTICE:
        // This is a mock implementation and it
        // should not be used as a implementation guideline.
        $entities = $this->select($queryFilter);
        foreach ($entities as $entity) {
            $this->delete($entity);
        }
    }
}
