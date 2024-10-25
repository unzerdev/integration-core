<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use UnzerSDK\Resources\Payment;

/**
 * Class PaymentSDK.
 *
 * @package BusinessLogic\Common\Mocks
 */
class PaymentSDK extends Payment
{
    /**
     * @param int $state
     *
     * @return self
     */
    public function setState(int $state): self
    {
        return parent::setState($state);
    }
}
