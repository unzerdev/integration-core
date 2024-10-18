<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use Unzer\Core\Infrastructure\Singleton;
use UnzerSDK\Unzer;

/**
 * Class UnzerFactoryMock.
 *
 * @package BusinessLogic\Common\IntegrationMocks
 */
class UnzerFactoryMock extends UnzerFactory
{
    protected static ?Singleton $instance = null;

    /**
     * @var ?UnzerMock
     */
    private ?UnzerMock $unzerMock = null;

    /**
     * @param ConnectionSettings|null $connectionSettings
     *
     * @return Unzer
     */
    public function makeUnzerAPI(?ConnectionSettings $connectionSettings = null): Unzer
    {
        return $this->unzerMock;
    }

    /**
     * @param UnzerMock $unzerMock
     *
     * @return void
     */
    public function setMockUnzer(UnzerMock $unzerMock): self
    {
        $this->unzerMock = $unzerMock;

        return $this;
    }

    public function getMockUnzer(): UnzerMock
    {
        return $this->unzerMock;
    }
}
