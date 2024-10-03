<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Unzer\Core\BusinessLogic\UnzerAPI\UnzerFactory;
use UnzerSDK\Unzer;

/**
 * Class UnzerFactoryMock.
 *
 * @package BusinessLogic\Common\IntegrationMocks
 */
class UnzerFactoryMock extends UnzerFactory
{
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
    public function setMockUnzer(UnzerMock $unzerMock): void
    {
        $this->unzerMock = $unzerMock;
    }
}
