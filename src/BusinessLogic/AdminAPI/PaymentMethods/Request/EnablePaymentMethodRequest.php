<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;

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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
