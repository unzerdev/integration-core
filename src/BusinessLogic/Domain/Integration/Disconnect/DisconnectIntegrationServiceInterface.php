<?php

namespace Unzer\Core\BusinessLogic\Domain\Integration\Disconnect;

/**
 * Interface IntegrationServiceInterface.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Integration\Disconnect
 */
interface DisconnectIntegrationServiceInterface
{
    /**
     * Deletes integration (platform) specific data for the current store context.
     *
     * @return void
     */
    public function deleteIntegrationData(): void;

    /**
     * Whether shared payment page settings may be deleted on disconnect.
     *
     * @return bool
     */
    public function shouldDeletePayPageSettings(): bool;
}
