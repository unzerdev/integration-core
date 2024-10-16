<?php

namespace Unzer\Core\BusinessLogic\AdminAPI\Connection\Controller;

use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\GetConnectionDataRequest;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\GetCredentialsRequest;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Response\ConnectionResponse;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Response\GetConnectionDataResponse;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Response\GetCredentialsResponse;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Response\ReRegisterWebhooksResponse;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidKeypairException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PrivateKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PublicKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class ConnectionController.
 *
 * @package Unzer\Core\BusinessLogic\AdminAPI\Connection\Controller
 */
class ConnectionController
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
     * @param ConnectionRequest $connectionRequest
     *
     * @return ConnectionResponse
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidKeypairException
     * @throws InvalidModeException
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     */
    public function connect(ConnectionRequest $connectionRequest): ConnectionResponse
    {
        $this->connectionService->initializeConnection($connectionRequest->toDomainModel());

        return new ConnectionResponse();
    }

    /**
     * @param GetConnectionDataRequest $connectionRequest
     *
     * @return GetConnectionDataResponse
     *
     * @throws InvalidModeException
     */
    public function getConnectionData(GetConnectionDataRequest $connectionRequest): GetConnectionDataResponse
    {
        $mode = Mode::parse($connectionRequest->getMode());
        $connectionSettings = $this->connectionService->getConnectionSettings();

        return new GetConnectionDataResponse($this->getConnectionDataFromConnectionSettings($mode, $connectionSettings));
    }

    /**
     * @return ReRegisterWebhooksResponse
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function reRegisterWebhooks(): ReRegisterWebhooksResponse
    {
        $webhookData = $this->connectionService->reRegisterWebhooks();

        return new ReRegisterWebhooksResponse($webhookData);
    }

    /**
     * @param GetCredentialsRequest $request
     *
     * @return GetCredentialsResponse
     *
     * @throws InvalidModeException
     */
    public function getCredentials(GetCredentialsRequest $request): GetCredentialsResponse
    {
        $mode = Mode::parse($request->getMode());
        $connectionSettings = $this->connectionService->getConnectionSettings();
        $connectionData = $this->getConnectionDataFromConnectionSettings($mode, $connectionSettings);
        $webhookData = $this->connectionService->getWebhookData();

        return new GetCredentialsResponse($connectionData, $webhookData);

    }

    /**
     * @param Mode $mode
     * @param ConnectionSettings|null $connectionSettings
     *
     * @return ConnectionData|null
     */
    private function getConnectionDataFromConnectionSettings(
        Mode $mode,
        ConnectionSettings $connectionSettings = null
    ): ?ConnectionData {
        if (!$connectionSettings) {
            return null;
        }

        if ($mode->equal(Mode::live())) {

            return $connectionSettings->getLiveConnectionData();
        }

        return $connectionSettings->getSandboxConnectionData();
    }
}
