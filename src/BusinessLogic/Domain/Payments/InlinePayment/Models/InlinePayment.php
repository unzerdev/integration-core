<?php

namespace Unzer\Core\BusinessLogic\Domain\Payments\InlinePayment\Models;

use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;

class InlinePayment
{

    protected ?Charge $charge;
    protected ?Authorization $authorization;

    /**
     * @param Charge|null $charge
     * @param Authorization|null $authorization
     */
    public function __construct(?Charge $charge = null, ?Authorization $authorization = null)
    {
        $this->charge = $charge;
        $this->authorization = $authorization;
    }

    /**
     * @return Charge|null
     */
    public function getCharge(): ?Charge
    {
        return $this->charge;
    }

    /**
     * @return Authorization|null
     */
    public function getAuthorization(): ?Authorization
    {
        return $this->authorization;
    }

    /**
     * @return Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->charge ? $this->charge->getPayment() : ($this->authorization ? $this->authorization->getPayment() : null);
    }
}