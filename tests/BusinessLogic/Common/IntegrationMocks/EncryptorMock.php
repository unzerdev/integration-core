<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use RuntimeException;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;

/**
 * Class EncryptorMock.
 *
 * @package Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks
 */
class EncryptorMock implements EncryptorInterface
{
    private bool $throwOnEncrypt = false;
    private bool $throwOnDecrypt = false;

    /**
     * @inheritDoc
     */
    public function encrypt(string $data): string
    {
        if ($this->throwOnEncrypt) {
            throw new RuntimeException('Encryption failed');
        }

        return $data . '.';
    }

    /**
     * @inheritDoc
     */
    public function decrypt(string $encryptedData): string
    {
        if ($this->throwOnDecrypt) {
            throw new RuntimeException('Decryption failed');
        }

        return substr($encryptedData, 0, -1);
    }

    /**
     * @param bool $throw
     *
     * @return void
     */
    public function setThrowOnEncrypt(bool $throw): void
    {
        $this->throwOnEncrypt = $throw;
    }

    /**
     * @param bool $throw
     *
     * @return void
     */
    public function setThrowOnDecrypt(bool $throw): void
    {
        $this->throwOnDecrypt = $throw;
    }
}
