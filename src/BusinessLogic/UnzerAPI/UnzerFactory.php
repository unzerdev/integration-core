<?php

namespace Unzer\Core\BusinessLogic\UnzerAPI;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\Singleton;
use UnzerSDK\Unzer;

/**
 * Class UnzerFactory.
 *
 * @package Unzer\Core\BusinessLogic\UnzerAPI
 */
class UnzerFactory
{
    private ?ConnectionSettings $connectionSettings = null;

    /**
     * @param ConnectionData|null $connectionData
     *
     * @return Unzer
     *
     * @throws ConnectionSettingsNotFoundException
     */
    public function makeUnzerAPI(?ConnectionData $connectionData = null): Unzer
    {
        if ($connectionData) {
            return $this->create($connectionData->getPrivateKey());
        }

        if (!$this->connectionSettings) {
            $this->connectionSettings = $this->getConnectionService()->getConnectionSettings();
        }

        if (!$this->connectionSettings) {
            throw new ConnectionSettingsNotFoundException(
                new TranslatableLabel('Connection settings not found.',
                    'connectionSettings.notFound')
            );
        }

        return $this->create($this->connectionSettings->getActiveConnectionData()->getPrivateKey());
    }

    protected function create(string $sdkKey): Unzer
    {
        return new Unzer($sdkKey);
    }

    /**
     * @return ConnectionService
     */
    protected function getConnectionService(): ConnectionService
    {
        return ServiceRegister::getService(ConnectionService::class);
    }
}
