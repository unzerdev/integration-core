<?php

namespace BusinessLogic\DataAccess\PaymentPageSettings\Entities;

use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings;
use Unzer\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class PaymentPageSettingsTest.
 *
 * @package BusinessLogic\DataAccess\PaymentPageSettings\Entities
 */
class PaymentPageSettingsTest extends GenericEntityTest
{
    public function getEntityClass(): string
    {
        return PaymentPageSettings::getClassName();
    }

}