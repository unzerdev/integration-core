<?php

namespace Unzer\Core\Tests\BusinessLogic\DataAccess\TransactionHistory\Entities;

use Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory;
use Unzer\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class TransactionHistoryEntityTest.
 *
 * @package BusinessLogic\Domain\TransactionHistory\Entities
 */
class TransactionHistoryEntityTest extends GenericEntityTest
{
    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return TransactionHistory::getClassName();
    }
}
