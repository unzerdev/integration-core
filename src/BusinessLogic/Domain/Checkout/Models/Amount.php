<?php

namespace Unzer\Core\BusinessLogic\Domain\Checkout\Models;

use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class Amount
 *
 * @package Unzer\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount
 */
class Amount
{
    /** @var int */
    private int $amount;

    /** @var Currency */
    private Currency $currency;

    /**
     * @param int $amount
     * @param Currency $currency
     */
    private function __construct(int $amount, Currency $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Instantiate amount object from float value
     *
     * @param float $amount
     * @param Currency $currency
     * @return static
     */
    public static function fromFloat(float $amount, Currency $currency): Amount
    {
        return new self(
            (int)round($amount * (10 ** $currency->getMinorUnits())),
            $currency
        );
    }

    /**
     * Instantiate amount object from smallest units (integer)
     *
     * @param int $amount
     * @param Currency $currency
     * @return static
     */
    public static function fromInt(int $amount, Currency $currency): Amount
    {
        return new self($amount, $currency);
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->amount;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return float
     */
    public function getPriceInCurrencyUnits()
    {
        return $this->amount / (10 ** $this->currency->getMinorUnits());
    }

    /**
     * Get amount
     *
     * @throws CurrencyMismatchException
     */
    public function minus(Amount $amount): Amount
    {
        if (!$this->getCurrency()->equal($amount->getCurrency())) {
            throw new CurrencyMismatchException(new TranslatableLabel('Currency mismatch.','checkout.currencyMismatch'));
        }
        return new self($this->getValue() - $amount->getValue(), $this->getCurrency());
    }

    /**
     * Get amount
     *
     * @throws CurrencyMismatchException
     */
    public function plus(Amount $amount): Amount
    {
        if (!$this->getCurrency()->equal($amount->getCurrency())) {
            throw new CurrencyMismatchException(new TranslatableLabel('Currency mismatch.','checkout.currencyMismatch'));
        }

        return new self($this->getValue() + $amount->getValue(), $this->getCurrency());
    }
}
