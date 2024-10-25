<?php

namespace Unzer\Core\BusinessLogic\Domain\Webhook\Models;

/**
 * Class WebhookPayload.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Webhook\Models
 */
class Webhook
{
    /** @var string */
    private string $retrieveUrl;

    /** @var string */
    private string $event;

    /** @var string */
    private string $publicKey;

    /** @var string */
    private string $paymentId;

    /**
     * @param string $retrieveUrl
     * @param string $event
     * @param string $publicKey
     * @param string $paymentId
     */
    public function __construct(string $retrieveUrl, string $event, string $publicKey, string $paymentId)
    {
        $this->retrieveUrl = $retrieveUrl;
        $this->event = $event;
        $this->publicKey = $publicKey;
        $this->paymentId = $paymentId;
    }

    /**
     * @return string
     */
    public function getRetrieveUrl(): string
    {
        return $this->retrieveUrl;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * @param string $payload
     *
     * @return Webhook
     */
    public static function fromPayload(string $payload): Webhook
    {
        $data = json_decode($payload, true);

        return new self(
            $data['retrieveUrl'] ?? '',
            $data['event'] ?? '',
            $data['publicKey'] ?? '',
            $data['paymentId'] ?? ''
        );
    }

    /**
     * @return string
     */
    public function toPayload(): string
    {
        $data = [
            'event' => $this->event,
            'publicKey' => $this->publicKey,
            'retrieveUrl' => $this->retrieveUrl,
            'paymentId' => $this->paymentId
        ];

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }
}
