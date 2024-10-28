<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Transaction\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;

/**
 * Class GetTransactionHistoryRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Transaction\Request
 */
class GetTransactionHistoryRequest extends Request
{
    /** @var string $orderId */
    private string $orderId;

    /**
     * @param string $orderId
     */
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }
}
