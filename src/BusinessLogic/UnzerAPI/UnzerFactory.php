<?php

namespace Unzer\Core\BusinessLogic\UnzerAPI;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
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

    public function create(string $sdkKey): Unzer
    {
        return new Unzer($sdkKey);
    }

    /**
     * @param ConnectionSettings|null $connectionSettings
     *
     * @return Unzer
     *
     * @throws ConnectionSettingsNotFoundException
     */
    public function makeUnzerAPI(?ConnectionSettings $connectionSettings = null): Unzer
    {
        if ($connectionSettings) {
            return $this->create($connectionSettings->getActiveConnectionData()->getPrivateKey());
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

    /**
     * @return ConnectionService
     */
    protected function getConnectionService(): ConnectionService
    {
        return ServiceRegister::getService(ConnectionService::class);
    }
}
