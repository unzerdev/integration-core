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
class UnzerFactory extends Singleton
{
    /**
     * @var Unzer|null
     */
    private static ?Unzer $unzer = null;

    /**
     * @param ConnectionSettings|null $connectionSettings
     *
     * @return Unzer
     *
     * @throws ConnectionSettingsNotFoundException
     */
    public function makeUnzerAPI(?ConnectionSettings $connectionSettings = null): Unzer
    {
        if (self::$unzer) {
            return self::$unzer;
        }

        if ($connectionSettings) {
            self::$unzer = new Unzer($connectionSettings->getActiveConnectionData()->getPrivateKey());

            return self::$unzer;
        }

        $connectionSettings = static::getConnectionService()->getConnectionSettings();

        if (!$connectionSettings) {
            throw new ConnectionSettingsNotFoundException(
                new TranslatableLabel('Connection settings not found.',
                    'connectionSettings.notFound')
            );
        }

        self::$unzer = new Unzer($connectionSettings->getActiveConnectionData()->getPrivateKey());

        return self::$unzer;
    }

    /**
     * @return void
     */
    public static function resetInstance(): void
    {
        parent::resetInstance();

        self::$unzer = null;
    }

    /**
     * @return ConnectionService
     */
    protected static function getConnectionService(): ConnectionService
    {
        return ServiceRegister::getService(ConnectionService::class);
    }
}
