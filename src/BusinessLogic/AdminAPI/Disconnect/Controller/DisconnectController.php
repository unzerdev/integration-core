<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Response\DisconnectResponse;
use Unzer\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class DisconnectController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Disconnect\Controller
 */
class DisconnectController
{
    /**
     * @var DisconnectService
     */
    private DisconnectService $disconnectService;

    /**
     * @param DisconnectService $disconnectService
     */
    public function __construct(DisconnectService $disconnectService)
    {
        $this->disconnectService = $disconnectService;
    }

    /**
     * @return DisconnectResponse
     *
     * @throws UnzerApiException
     */
    public function disconnect(): DisconnectResponse
    {
        $this->disconnectService->disconnect();

        return new DisconnectResponse();
    }
}
