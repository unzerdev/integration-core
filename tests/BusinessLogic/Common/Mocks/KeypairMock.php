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
     * @var array
     */
    public static array $types = [];

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

    /**
     * @param array $types
     *
     * @return void
     */
    public function setAvailablePaymentTypes(array $types): void
    {
        self::$types = $types;
    }

    /**
     * @return array
     */
    public function getAvailablePaymentTypes(): array
    {
        return self::$types;
    }
}
