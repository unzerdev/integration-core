<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;

/**
 * Class PaymentMethodsRequest.
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request
 */
class PaymentMethodsRequest extends Request
{
    /** @var string  */
    private string $billingCountry;

    /** @var string  */
    private string $currency;

    /** @var float  */
    private float $amount;

    /** @var string  */
    private string $locale;

    /**
     * @param string $billingCountry
     * @param string $currency
     * @param float $amount
     * @param string $locale
     */
    public function __construct(string $billingCountry, string $currency, float $amount, string $locale)
    {
        $this->billingCountry = $billingCountry;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->locale = $locale;
    }
}
