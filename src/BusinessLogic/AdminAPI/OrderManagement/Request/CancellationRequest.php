<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;

/**
 * Class CancellationRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request
 */
class CancellationRequest extends Request
{
    /** @var string $orderId */
    private string $orderId;

    /** @var Amount $amount */
    private Amount $amount;

    /**
     * @param string $orderId
     * @param Amount $amount
     */
    public function __construct(string $orderId, Amount $amount)
    {
        $this->orderId = $orderId;
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }
}
