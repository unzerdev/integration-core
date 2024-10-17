<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;

/**
 * Class PaymentMethodsRequest.
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request
 */
class PaymentMethodsRequest extends Request
{
    /** @var string  */
    private string $billingCountry;

    /** @var Amount  */
    private Amount $amount;

    /** @var string  */
    private string $locale;

    /**
     * @param string $billingCountry
     * @param Amount $amount
     * @param string $locale
     */
    public function __construct(string $billingCountry, Amount $amount, string $locale = '')
    {
        $this->billingCountry = $billingCountry;
        $this->amount = $amount;
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getBillingCountry(): string
    {
        return $this->billingCountry;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}
