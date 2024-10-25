<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use UnzerSDK\Resources\EmbeddedResources\Amount;

/**
 * Class SdkAmount.
 *
 * @package BusinessLogic\Common\Mocks
 */
class SdkAmount extends Amount
{
    /**
     * @param string $currency
     *
     * @return Amount
     */
    public function setCurrency(string $currency): Amount
    {
        return parent::setCurrency($currency);
    }

    /**
     * @param float $canceled
     *
     * @return Amount
     */
    public function setCanceled(float $canceled): Amount
    {
        return parent::setCanceled($canceled);
    }

    /**
     * @param float $remaining
     *
     * @return Amount
     */
    public function setRemaining(float $remaining): Amount
    {
        return parent::setRemaining($remaining);
    }

    /**
     * @param float $charged
     *
     * @return Amount
     */
    public function setCharged(float $charged): Amount
    {
        return parent::setCharged($charged);
    }

    /**
     * @param float $total
     *
     * @return Amount
     */
    public function setTotal(float $total): Amount
    {
        return parent::setTotal($total);
    }
}
