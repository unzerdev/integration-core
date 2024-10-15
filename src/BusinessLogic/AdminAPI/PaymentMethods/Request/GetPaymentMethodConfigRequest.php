<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;

/**
 * Class GetPaymentMethodConfigRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request
 */
class GetPaymentMethodConfigRequest extends Request
{
    /**
     * @var string
     */
    private string $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
