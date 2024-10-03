<?php

namespace BusinessLogic\Domain\Webhook\Models;

use Unzer\Core\BusinessLogic\Domain\Webhook\Models\WebhookData;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;
use UnzerSDK\Constants\WebhookEvents;
use UnzerSDK\Resources\Webhook;

/**
 * Class WebhookDataModelTest.
 *
 * @package BusinessLogic\Domain\Webhook\Models
 */
class WebhookDataModelTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testFromBatch(): void
    {
        // arrange

        $webhooks = [
            new Webhook('www.test.com', WebhookEvents::AUTHORIZE),
            new Webhook('www.test.com', WebhookEvents::CHARGE),
            new Webhook('www.test.com', WebhookEvents::PAYMENT)
        ];

        // act
        $webhookData = WebhookData::fromBatch($webhooks);

        // assert
        self::assertEquals('www.test.com', $webhookData->getUrl());
        self::assertCount(3, $webhookData->getEvents());
        self::assertCount(3, $webhookData->getIds());
        self::assertEquals(WebhookEvents::AUTHORIZE, $webhookData->getEvents()[0]);
        self::assertEquals(WebhookEvents::CHARGE, $webhookData->getEvents()[1]);
        self::assertEquals(WebhookEvents::PAYMENT, $webhookData->getEvents()[2]);
    }
}
