<?php

namespace Unzer\Core\Infrastructure\Logger\Interfaces;

use Unzer\Core\Infrastructure\Logger\LogData;

/**
 * Interface LoggerAdapter.
 *
 * @package Unzer\Core\Infrastructure\Logger\Interfaces
 */
interface LoggerAdapter
{
    /**
     * Log message in system
     *
     * @param LogData $data
     *
     * @return void
     */
    public function logMessage(LogData $data): void;
}
