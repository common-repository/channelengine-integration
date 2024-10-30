<?php

namespace ChannelEngine\Infrastructure\TaskExecution\Interfaces;

use ChannelEngine\Infrastructure\Serializer\Interfaces\Serializable;

/**
 * Interface Runnable.
 *
 * @package ChannelEngine\Infrastructure\TaskExecution\Interfaces
 */
interface Runnable extends Serializable
{
    /**
     * Starts runnable run logic
     */
    public function run();
}
