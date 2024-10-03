<?php

namespace Unzer\Core\BusinessLogic\Domain\Connection\Repositories;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;

/**
 * Interface ConnectionSettingsRepositoryInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Connection\Repositories
 */
interface ConnectionSettingsRepositoryInterface
{
    /**
     * @return ConnectionSettings|null
     */
    public function getConnectionSettings(): ?ConnectionSettings;

    /**
     * Sets connection settings.
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     */
    public function setConnectionSettings(ConnectionSettings $connectionSettings): void;

    /**
     * @return void
     */
    public function deleteConnectionSettings(): void;
}
