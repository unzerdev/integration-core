<?php

namespace Unzer\Core\BusinessLogic\UnzerAPI;

use RuntimeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\EncryptionFailedException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PrivateKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Unzer\Core\Infrastructure\ServiceRegister;
use UnzerSDK\Unzer;

/**
 * Class UnzerFactory.
 *
 * @package Unzer\Core\BusinessLogic\UnzerAPI
 */
class UnzerFactory
{
    /**
     * @var ConnectionData|null $connectionData
     */
    private ?ConnectionData $connectionData = null;

    /**
     * @param ConnectionData|null $connectionData
     *
     * @return Unzer
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws PrivateKeyInvalidException
     * @throws EncryptionFailedException
     */
    public function makeUnzerAPI(?ConnectionData $connectionData = null): Unzer
    {
        if ($connectionData) {
            return $this->createFromConnectionData($connectionData);
        }

        if (!$this->connectionData) {
            $this->connectionData = $this->getConnectionService()->getActiveConnectionData();
        }

        if (!$this->connectionData) {
            throw new ConnectionSettingsNotFoundException(
                new TranslatableLabel('Connection settings not found.',
                    'connectionSettings.notFound')
            );
        }

        return $this->createFromConnectionData($this->connectionData);
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return Unzer
     *
     * @throws PrivateKeyInvalidException
     */
    private function createFromConnectionData(ConnectionData $connectionData): Unzer
    {
        try {
            return $this->create($connectionData->getPrivateKey());
        } catch (RuntimeException $e) {
            throw new PrivateKeyInvalidException(
                new TranslatableLabel('Private key is invalid.', 'connection.invalidPrivateKey')
            );
        }
    }

    /**
     * @param string $sdkKey
     *
     * @return Unzer
     */
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
