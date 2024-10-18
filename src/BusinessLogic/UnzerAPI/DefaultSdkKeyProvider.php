<?php

namespace Unzer\Core\BusinessLogic\UnzerAPI;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class DefaultSdkKeyProvider
 *
 * @package Unzer\Core\BusinessLogic\UnzerAPI
 */
class DefaultSdkKeyProvider
{
    private ConnectionService $connectionService;

    public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }
    public function __invoke(): string
    {
        $connectionSettings = $this->connectionService->getConnectionSettings();

        if (!$connectionSettings) {
            throw new ConnectionSettingsNotFoundException(
                new TranslatableLabel('Connection settings not found.',
                    'connectionSettings.notFound')
            );
        }

        return $connectionSettings->getActiveConnectionData()->getPrivateKey();
    }
}
