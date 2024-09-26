<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\Logger;

use Unzer\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Unzer\Core\Infrastructure\Logger\LogData;

/**
 * Class TestDefaultLogger.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\Logger
 */
class TestDefaultLogger implements ShopLoggerAdapter
{
    /**
     * @var LogData
     */
    public LogData $data;

    /**
     * @var LogData[]
     */
    public array $loggedMessages = [];

    /**
     * @param LogData $data
     *
     * @return void
     */
    public function logMessage(LogData $data): void
    {
        $this->data = $data;
        $this->loggedMessages[] = $data;
    }

    /**
     * @param $message
     *
     * @return bool
     */
    public function isMessageContainedInLog($message): bool
    {
        foreach ($this->loggedMessages as $loggedMessage) {
            if (mb_strpos($loggedMessage->getMessage(), $message) !== false) {
                return true;
            }
        }

        return false;
    }
}
