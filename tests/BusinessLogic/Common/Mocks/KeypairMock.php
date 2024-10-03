<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\Mocks;

use UnzerSDK\Resources\Keypair;

/**
 * Class KeypairMock.
 *
 * @package BusinessLogic\Common\Mocks
 */
class KeypairMock extends Keypair
{
    /**
     * @var string|null
     */
    public ?string $publicKey = null;

    /**
     * @return string|null
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     *
     * @return void
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }
}
