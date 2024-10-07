<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\State\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\State\Request\StateRequest;
use Unzer\Core\BusinessLogic\AdminAPI\State\Response\StateResponse;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;

class StateController
{
    /**
     * @var ConnectionService
     */
    private ConnectionService $connectionService;

    /**
     * @param ConnectionService $connectionService
     */
    public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * Check if user is loggedIn. If true return true, else return false.
     *
     * @param StateRequest $request
     *
     * @return StateResponse
     *
     * @throws InvalidModeException
     */
    public function isLoggedIn(StateRequest $request): StateResponse
    {
        $mode = Mode::parse($request->getMode());

        return new StateResponse($this->connectionService->isLoggedIn($mode));
    }
}