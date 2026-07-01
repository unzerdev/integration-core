<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectIntegrationServiceInterface;

/**
 * Class IntegrationServiceMock.
 *
 * @package BusinessLogic\Common\IntegrationMocks
 */
class DisconnectIntegrationServiceMock implements DisconnectIntegrationServiceInterface
{
    /**
     * @var bool
     */
    private bool $shouldDeletePayPageSettings = true;

    /**
     * @inheritDoc
     */
    public function deleteIntegrationData(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function shouldDeletePayPageSettings(): bool
    {
        return $this->shouldDeletePayPageSettings;
    }

    /**
     * @param bool $shouldDeletePayPageSettings
     *
     * @return void
     */
    public function setShouldDeletePayPageSettings(bool $shouldDeletePayPageSettings): void
    {
        $this->shouldDeletePayPageSettings = $shouldDeletePayPageSettings;
    }
}
