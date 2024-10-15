<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Models\PaymentMethodConfig;

/**
 * Class EnablePaymentMethodRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request
 */
class EnablePaymentMethodRequest extends Request
{
    /** @var string */
    private string $type;

    /** @var bool */
    private bool $enabled;

    /**
     * @param string $type
     * @param bool $enabled
     */
    public function __construct(string $type, bool $enabled)
    {
        $this->type = $type;
        $this->enabled = $enabled;
    }

    /**
     * @return PaymentMethodConfig
     */
    public function toDomainModel(): PaymentMethodConfig
    {
        return new PaymentMethodConfig($this->type, $this->enabled);
    }
}
