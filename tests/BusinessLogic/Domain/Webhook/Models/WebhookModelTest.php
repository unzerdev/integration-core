<?php

namespace Unzer\Core\Tests\BusinessLogic\Domain\Webhook\Models;

use Unzer\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Unzer\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class WebhookPayloadModelTest.
 *
 * @package BusinessLogic\Domain\Webhook\Models
 */
class WebhookModelTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testFromPayload(): void
    {
        // arrange
        $payload = '{
  "event" : "payment.canceled",
  "publicKey" : "s-pub-test",
  "retrieveUrl" : "https://test/v1/payments/s-pay-test",
  "paymentId" : "s-pay-test"
}';
        // act
        $webhookPayload = Webhook::fromPayload($payload);

        // assert
        self::assertEquals('payment.canceled', $webhookPayload->getEvent());
        self::assertEquals('s-pub-test', $webhookPayload->getPublicKey());
        self::assertEquals('https://test/v1/payments/s-pay-test', $webhookPayload->getRetrieveUrl());
        self::assertEquals('s-pay-test', $webhookPayload->getPaymentId());
    }

    /**
     * @return void
     */
    public function testToPayload(): void
    {
        // arrange
        $webhookPayload = new Webhook(
            'https://test/v1/payments/s-pay-test',
            'payment.canceled',
            's-pub-test',
            's-pay-test'
        );

        // act
        $payload = $webhookPayload->toPayload();

        // assert
        $expectedPayload = '{"event":"payment.canceled","publicKey":"s-pub-test","retrieveUrl":"https://test/v1/payments/s-pay-test","paymentId":"s-pay-test"}';

        self::assertEquals($expectedPayload, $payload);
    }
}
