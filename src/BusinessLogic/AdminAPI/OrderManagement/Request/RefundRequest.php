<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;

/**
 * Class RefundRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request
 */
class RefundRequest extends Request
{
    /**
     * @var string $orderId
     */
    private string $orderId;

    /**
     * @var Amount $amount
     */
    private Amount $amount;

    /**
     * @var string|null $reference
     */
    private ?string $reference;

    /**
     * @param string $orderId
     * @param Amount $amount
     * @param string|null $reference
     */
    public function __construct(string $orderId, Amount $amount, ?string $reference = null)
    {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->reference = $reference;
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

    /**
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }
}
