<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use UnzerSDK\Resources\Payment;

/**
 * Class PaymentResponse
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response
 */
class PaymentResponse extends Response
{
    private Payment $payment;

    /**
     * Payment constructor.
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->payment->getId(),
            'state' => $this->payment->getState(),
            'stateName' => $this->payment->getStateName(),
            'currency' => $this->payment->getAmount()->getCurrency(),
            'total' => $this->payment->getAmount()->getTotal(),
        ];
    }
}
