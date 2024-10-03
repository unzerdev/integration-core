<?php

namespace Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks;

use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;

/**
 * Class EncryptorMock.
 *
 * @package Unzer\Core\Tests\BusinessLogic\Common\IntegrationMocks
 */
class EncryptorMock implements EncryptorInterface
{
    /**
     * @inheritDoc
     */
    public function encrypt(string $data): string
    {
        return $data . '.';
    }

    /**
     * @inheritDoc
     */
    public function decrypt(string $encryptedData): string
    {
        return substr($encryptedData, 0, -1);
    }
}
