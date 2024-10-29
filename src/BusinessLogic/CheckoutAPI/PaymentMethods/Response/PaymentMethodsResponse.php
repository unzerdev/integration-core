<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;

/**
 * Class PaymentMethodsResponse.
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Response
 */
class PaymentMethodsResponse extends Response
{
    /**
     * @var PaymentMethodConfig[]
     */
    private array $paymentMethods;

    /**
     * @var string
     */
    private string $locale;

    /**
     * @param PaymentMethodConfig[] $paymentMethods
     * @param string $locale
     */
    public function __construct(array $paymentMethods, string $locale)
    {
        $this->paymentMethods = $paymentMethods;
        $this->locale = $locale;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_map(function ($paymentMethod) {
            $formattedMethod = [
                'type' => $paymentMethod->getType(),
                'name' => $paymentMethod->getNameByLocale($this->locale),
                'description' => ''
            ];

            if ($description = $paymentMethod->getDescriptionByLocale($this->locale)) {
                $formattedMethod['description'] = $description;
            }

            if ($surcharge = $paymentMethod->getSurcharge()) {
                $formattedMethod['surcharge'] = [
                    'value' => $surcharge->getValue(),
                    'currency' => $surcharge->getCurrency(),
                ];
            }

            return $formattedMethod;
        }, $this->paymentMethods);
    }
}
