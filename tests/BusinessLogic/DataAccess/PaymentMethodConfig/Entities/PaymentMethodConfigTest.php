<?php

namespace Unzer\Core\Tests\BusinessLogic\DataAccess\PaymentMethodConfig\Entities;

use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig;
use Unzer\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class PaymentMethodConfigTest.
 *
 * @package Unzer\Core\Tests\BusinessLogic\DataAccess\PaymentMethodConfig\Entities
 */
class PaymentMethodConfigTest extends GenericEntityTest
{
    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return PaymentMethodConfig::getClassName();
    }
}
