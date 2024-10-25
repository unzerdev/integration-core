<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models\PaymentState;

/**
 * Class PaymentStateResponse
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response
 */
class PaymentStateResponse extends Response
{
    private PaymentState $paymentState;

    /**
     * PaymentStateResponse constructor.
     * @param PaymentState $paymentState
     */
    public function __construct(PaymentState $paymentState)
    {

        $this->paymentState = $paymentState;
    }
    public function getPaymentState(): PaymentState
    {
        return $this->paymentState;
    }

    public function toArray(): array
    {
        return $this->paymentState->toArray();
    }
}
