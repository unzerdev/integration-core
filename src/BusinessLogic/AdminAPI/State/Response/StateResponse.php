<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\State\Response;

use Unzer\Core\BusinessLogic\ApiFacades\Response\Response;

/**
 * Class StateResponse.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\State\Response
 */
class StateResponse extends Response
{
    /**
     * Flag representing login state.
     */
    private bool $loggedIn = false;

    /**
     * @param bool $loggedIn
     */
    public function __construct(bool $loggedIn)
    {
        $this->loggedIn = $loggedIn;
    }

    /**
     *  Transforms loggedIn flag to array.
     *
     * @return array Array representation of state.
     */
    public function toArray(): array
    {
        return [
            'loggedIn' => $this->loggedIn,
        ];
    }
}