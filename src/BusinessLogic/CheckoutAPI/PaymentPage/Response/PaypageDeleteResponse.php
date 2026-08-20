<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;

/**
 * Class PaypageDeleteResponse
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response
 */
class PaypageDeleteResponse extends Response
{
    private string $paypageId;

    /**
     * PaypageDeleteResponse constructor.
     *
     * @param string $paypageId
     */
    public function __construct(string $paypageId)
    {
        $this->paypageId = $paypageId;
    }

    /**
     * @return string
     */
    public function getPaypageId(): string
    {
        return $this->paypageId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->paypageId
        ];
    }
}
