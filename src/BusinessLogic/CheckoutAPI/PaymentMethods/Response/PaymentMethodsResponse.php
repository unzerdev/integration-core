<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;

/**
 * Class PaymentMethodsResponse.
 *
 * @package Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Response
 */
class PaymentMethodsResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            [
             'type' => PaymentMethodTypes::CARDS,
             'name' => 'Unzer Cards',
             'description' => 'A cards payment method',
            ],
            [
                'type' => PaymentMethodTypes::EPS,
                'name' => 'Unzer EPS',
                'description' => 'Eps payment method',
            ],
            [
                'type' => PaymentMethodTypes::KLARNA,
                'name' => 'Unzer Klarna',
                'description' => 'Klarna payment method',
            ]
        ];
    }
}
