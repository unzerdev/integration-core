<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\State\Request;

use Unzer\Core\BusinessLogic\ApiFacades\Request\Request;

/**
 * Class StateRequest.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\State\Request
 */
class StateRequest extends Request
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