<?php

namespace ChannelEngine\Infrastructure\TaskExecution\Interfaces;

/**
 * Interface Priority
 *
 * @package ChannelEngine\Infrastructure\TaskExecution\Interfaces
 */
interface Priority
{
    /**
     * Indicates 'low' queue item execution priority.
     */
    const LOW = 1;
    /**
     * Indicates 'normal' queue item execution priority.
     */
    const NORMAL = 100;
    /**
     * Indicates 'high' queue item execution priority.
     */
    const HIGH = 1000;
}