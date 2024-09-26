<?php

namespace Unzer\Core\Infrastructure\Logger\Interfaces;

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
