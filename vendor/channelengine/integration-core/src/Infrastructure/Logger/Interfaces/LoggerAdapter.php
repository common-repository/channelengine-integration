<?php

namespace ChannelEngine\Infrastructure\Logger\Interfaces;

use ChannelEngine\Infrastructure\Logger\LogData;

/**
 * Interface LoggerAdapter.
 *
 * @package ChannelEngine\Infrastructure\Logger\Interfaces
 */
interface LoggerAdapter
{
    /**
     * Log message in system
     *
     * @param LogData $data
     */
    public function logMessage(LogData $data);
}
