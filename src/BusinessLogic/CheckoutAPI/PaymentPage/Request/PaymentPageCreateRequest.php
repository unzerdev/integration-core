<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request;

use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Request\CommonFlowRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;

/**
 * Class PaymentPageCreateRequest
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request
 */
class PaymentPageCreateRequest extends CommonFlowRequest
{
    private string $paymentMethodType;
    private string $orderId;
    private Amount $amount;
    private string $returnUrl;
    private array $sessionData;
    private string $locale;

    /**
     * PaymentPageCreateRequest constructor.
     * @param string $paymentMethodType
     * @param string $orderId
     * @param Amount $amount
     * @param string $returnUrl
     * @param string $mode
     * @param array $sessionData
     * @param string $locale
     */
    public function __construct(
        string $paymentMethodType,
        string $orderId,
        Amount $amount,
        string $returnUrl,
        array $sessionData = [],
        string $locale = 'default'
    ) {
        $this->paymentMethodType = $paymentMethodType;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
        $this->sessionData = $sessionData;
        $this->locale = $locale;
    }

    public function getPaymentMethodType(): string
    {
        return $this->paymentMethodType;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    public function getSessionData(): array
    {
        return $this->sessionData;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
