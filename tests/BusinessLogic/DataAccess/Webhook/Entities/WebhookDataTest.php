<?php

namespace Unzer\Core\Tests\BusinessLogic\DataAccess\Webhook\Entities;

use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookData;
use Unzer\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class WebhookDataTest.
 *
 * @package BusinessLogic\DataAccess\Webhook
 */
class WebhookDataTest extends GenericEntityTest
{
    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return WebhookData::getClassName();
    }
}
