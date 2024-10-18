<?php

namespace Unzer\Core\Tests\BusinessLogic\DataAccess\PaymentStatusMap\Entities;

use Unzer\Core\BusinessLogic\DataAccess\PaymentStatusMap\Entities\PaymentStatusMap;
use Unzer\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class PaymentStatusMAp.
 *
 * @package BusinessLogic\DataAccess\PaymentStatusMap\Entities
 */
class PaymentStatusMapTest extends GenericEntityTest
{
    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return PaymentStatusMap::getClassName();
    }
}
