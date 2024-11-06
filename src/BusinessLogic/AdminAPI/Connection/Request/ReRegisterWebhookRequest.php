<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;

/**
 * Class ReRegisterWebhookRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Connection\Request
 */
class ReRegisterWebhookRequest extends Request
{
    /**
     * @var string
     */
    private string $mode;

    /**
     * @param string $mode
     */
    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }
}
