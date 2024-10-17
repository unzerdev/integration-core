<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\Currency;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;

/**
 * Interface CurrencyServiceInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\Currency
 */
interface CurrencyServiceInterface
{
    /**
     * @return Currency
     */
    public function getDefaultCurrency(): Currency;

    /**
     * Returns amount in currency given as second parameter.
     *
     * @param Amount $baseAmount
     * @param Currency $targetCurrency
     *
     * @return Amount
     */
    public function convert(Amount $baseAmount, Currency $targetCurrency): Amount;
}
