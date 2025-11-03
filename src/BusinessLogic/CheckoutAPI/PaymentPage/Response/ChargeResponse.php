<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use UnzerSDK\Resources\TransactionTypes\Charge;

/**
 * Class ChargeResponse
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Response
 */
class ChargeResponse extends Response
{
    private Charge $charge;

    /**
     * Charge constructor.
     * @param Charge $charge
     */
    public function __construct(Charge $charge)
    {
        $this->charge = $charge;
    }

    /**
     * @return Charge
     */
    public function getCharge(): Charge
    {
        return $this->charge;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->charge->getId()
        ];
    }
}
