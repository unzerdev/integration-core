<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\InlinePayment\Request;

use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Request\CommonFlowRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\BookingMethod;

class InlinePaymentCreateRequest extends CommonFlowRequest
{

    private string $paymentMethodType;
    private string $orderId;
    private Amount $amount;
    private string $returnUrl;
    private array $sessionData;
    private string $locale;
    private ?BookingMethod $bookingMethod;

    /**
     * @param string $paymentMethodType
     * @param string $orderId
     * @param Amount $amount
     * @param string $returnUrl
     * @param array $sessionData
     * @param string $locale
     * @param BookingMethod|null $bookingMethod
     */
    public function __construct(
        string $paymentMethodType,
        string $orderId,
        Amount $amount,
        string $returnUrl,
        array $sessionData = [],
        string $locale = 'default',
        ?BookingMethod $bookingMethod = null
    ) {
        $this->paymentMethodType = $paymentMethodType;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
        $this->sessionData = $sessionData;
        $this->locale = $locale;
        $this->bookingMethod = $bookingMethod;
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

    public function getBookingMethod(): ?BookingMethod
    {
        return $this->bookingMethod;
    }
}
