<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;

/**
 * Class GetConnectionDataRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Connection\Request
 */
class GetConnectionDataRequest extends Request
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
