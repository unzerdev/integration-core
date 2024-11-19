<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Keypair;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\Paypage;
use UnzerSDK\Resources\Webhook;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\AbstractUnzerResource;

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

    /** @var AbstractUnzerResource|null */
    private ?AbstractUnzerResource $resource = null;

    /**
     * @var ?Payment $payment
     */
    private ?Payment $payment = null;

    /**
     * @var Basket|null
     */
    private ?Basket $basket = null;

    /**
     * @var Metadata|null
     */
    private ?Metadata $metadata = null;

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
     * @param $webhook
     *
     * @return void
     */
    public function deleteWebhook($webhook)
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

    public function setPayPageData(array $payPageData): UnzerMock
    {
        $this->payPageData = $payPageData;

        return $this;
    }

    public function initPayPageAuthorize(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage {
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
    ): Paypage {
        $this->callHistory['initPayPageCharge'][] = ['paypage' => $paypage];

        $result = new PaypageMock($paypage->getAmount(), $paypage->getCurrency(), $paypage->getReturnUrl());
        $result->setId($this->payPageData['id'] ?? null);
        $result->setRedirectUrl($this->payPageData['redirectUrl'] ?? null);
        $result->setPaymentId($this->payPageData['paymentId'] ?? null);

        return $result;
    }

    public function createOrUpdateCustomer(Customer $customer): Customer
    {
        $this->callHistory['createOrUpdateCustomer'][] = ['customer' => $customer];

        return $customer;
    }

    public function createBasket(Basket $basket): Basket
    {
        $this->callHistory['createBasket'][] = ['basket' => $this->basket];

        return $basket;
    }

    public function createMetadata(Metadata $metadata): Metadata
    {
        $this->callHistory['createMetadata'][] = ['metadata' => $this->metadata];

        return $metadata;
    }

    /**
     * @param $payment
     * @param float|null $amount
     * @param string|null $orderId
     * @param string|null $invoiceId
     *
     * @return Charge
     */
    public function chargeAuthorization(
        $payment,
        float $amount = null,
        string $orderId = null,
        string $invoiceId = null
    ): Charge {
        $this->callHistory['chargeAuthorization'][] = ['payment' => $payment, 'amount' => $amount];

        return new Charge();
    }

    /**
     * @param $payment
     * @param float|null $amount
     *
     * @return Cancellation
     */
    public function cancelAuthorizationByPayment($payment, float $amount = null): Cancellation
    {
        $this->callHistory['cancelAuthorizationByPayment'][] = ['payment' => $payment, 'amount' => $amount];

        return new Cancellation();
    }

    /**
     * @param $payment
     * @param string $chargeId
     * @param float|null $amount
     * @param string|null $reasonCode
     * @param string|null $referenceText
     * @param float|null $amountNet
     * @param float|null $amountVat
     *
     * @return Cancellation
     */
    public function cancelChargeById(
        $payment,
        string $chargeId,
        float $amount = null,
        string $reasonCode = null,
        string $referenceText = null,
        float $amountNet = null,
        float $amountVat = null
    ): Cancellation {
        $this->callHistory['cancelChargeById'][] = [
            'payment' => $payment,
            'chargeId' => $chargeId,
            'amount' => $amount
        ];

        return new Cancellation();
    }

    /**
     * @param BasePaymentType $paymentType
     *
     * @return BasePaymentType
     */
    public function createPaymentType(BasePaymentType $paymentType): BasePaymentType
    {
        return new Card('123', '03/30');
    }

    public function fetchResourceFromEvent(string $eventJson = null): AbstractUnzerResource
    {
        $this->callHistory['fetchResourceFromEvent'][] = ['event' => $eventJson];

        return $this->resource;
    }

    /**
     * @param AbstractUnzerResource $resource
     *
     * @return void
     */
    public function setResourceFromEvent(AbstractUnzerResource $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * @param $payment
     *
     * @return Payment
     */
    public function fetchPayment($payment): Payment
    {
        $this->callHistory['fetchPayment'][] = ['paymentId' => $payment];

        return $this->payment;
    }

    /**
     * @param Payment $payment
     *
     * @return void
     */
    public function setPayment(Payment $payment): void
    {
        $this->payment = $payment;
    }

    /**
     * @param \UnzerSDK\Resources\V2\Paypage $paypage
     *
     * @return \UnzerSDK\Resources\V2\Paypage
     */
    public function createPaypage(\UnzerSDK\Resources\V2\Paypage $paypage): \UnzerSDK\Resources\V2\Paypage
    {
        $this->callHistory['createPaypage'][] = ['paypage' => $paypage];

        $mockPaypage = new \UnzerSDK\Resources\V2\Paypage(
            $paypage->getAmount(),
            $paypage->getCurrency(),
            $paypage->getMode()
        );
        $mockPaypage->setId($this->payPageData['id'] ?? null);
        $mockPaypage->setRedirectUrl($this->payPageData['redirectUrl']);

        return $mockPaypage;
    }
}
