<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Keypair;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\PaymentTypes\Paypage;
use UnzerSDK\Resources\Webhook;
use UnzerSDK\Unzer;

/**
 * Class UnzerMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class UnzerMock extends Unzer
{
    private $callHistory = [];

    /**
     * @var Keypair|null
     */
    private ?Keypair $keypair = null;

    /**
     * @var Webhook[]
     */
    private array $webhooks = [];
    private ?array $payPageData = [];

    public function getMethodCallHistory($methodName)
    {
        return !empty($this->callHistory[$methodName]) ? $this->callHistory[$methodName] : [];
    }

    /**
     * @param Keypair $keypair
     *
     * @return void
     */
    public function setKeypair(Keypair $keypair): void
    {
        $this->keypair = $keypair;
    }

    /**
     * @param bool $detailed
     *
     * @return Keypair
     */
    public function fetchKeypair(bool $detailed = false): Keypair
    {
        return $this->keypair;
    }

    /**
     * @param string $url
     * @param array $events
     *
     * @return array
     */
    public function registerMultipleWebhooks(string $url, array $events): array
    {
        return $this->webhooks;
    }

    /**
     * @return void
     */
    public function deleteAllWebhooks(): void
    {
    }

    /**
     * @return array
     */
    public function fetchAllWebhooks(): array
    {
        return $this->webhooks;
    }

    /**
     * @param array $webhooks
     *
     * @return void
     */
    public function setWebhooks(array $webhooks): void
    {
        $this->webhooks = $webhooks;
    }

    public function setPayPageData(array $payPageData)
    {
        $this->payPageData = $payPageData;
    }

    public function initPayPageAuthorize(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage
    {
        $this->callHistory['initPayPageAuthorize'][] = ['paypage' => $paypage];

        $result = new PaypageMock($paypage->getAmount(), $paypage->getCurrency(), $paypage->getReturnUrl());
        $result->setId($this->payPageData['id'] ?? null);
        $result->setRedirectUrl($this->payPageData['redirectUrl'] ?? null);
        $result->setPaymentId($this->payPageData['paymentId'] ?? null);

        return $result;
    }

    public function initPayPageCharge(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage
    {
        $this->callHistory['initPayPageCharge'][] = ['paypage' => $paypage];

        $result = new PaypageMock($paypage->getAmount(), $paypage->getCurrency(), $paypage->getReturnUrl());
        $result->setId($this->payPageData['id'] ?? null);
        $result->setRedirectUrl($this->payPageData['redirectUrl'] ?? null);
        $result->setPaymentId($this->payPageData['paymentId'] ?? null);

        return $result;
    }
}
