<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethod;

/**
 * Class PaymentMethodsResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Response
 */
class PaymentMethodsResponse extends Response
{
    /**
     * @var PaymentMethod[]
     */
    private array $paymentMethods;

    /**
     * @param PaymentMethod[] $paymentMethods
     */
    public function __construct(array $paymentMethods = [])
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_map(function ($paymentMethod) {
            return [
                'type' => $paymentMethod->getType(),
                'name' => $paymentMethod->getName(),
                'description' => $paymentMethod->getDescription(),
                'enabled' => $paymentMethod->isEnabled(),
            ];
        }, $this->paymentMethods);
    }
}
