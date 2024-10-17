<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Integration\Currency\CurrencyServiceInterface;

/**
 * Class CurrencyServiceMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class CurrencyServiceMock implements CurrencyServiceInterface
{
    /**
     * @var Currency|null
     */
    private ?Currency $currency = null;

    /**
     * @inheritDoc
     */
    public function getDefaultCurrency(): Currency
    {
        return $this->currency ?: Currency::getDefault();
    }

    /**
     * @inheritDoc
     */
    public function convert(Amount $baseAmount, Currency $targetCurrency): Amount
    {
        return $baseAmount;
    }

    /**
     * @param Currency $currency
     *
     * @return void
     */
    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }
}
