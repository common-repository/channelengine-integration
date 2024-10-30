<?php

namespace ChannelEngine\BusinessLogic\Utility\Listeners;

use ChannelEngine\BusinessLogic\Configuration\ConfigService;
use ChannelEngine\BusinessLogic\Utility\Tasks\ObsoleteLogsRemover;
use ChannelEngine\BusinessLogic\Utility\Tasks\ObsoleteTaskDeleter;
use ChannelEngine\Infrastructure\Configuration\Configuration;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;

/**
 * Class SystemCleanupListener
 *
 * @package ChannelEngine\BusinessLogic\Utility\Listeners
 */
class SystemCleanupListener
{
    const CLEANUP_PERIOD = 86400;

    public static function handle()
    {
        if (!static::canHandle()) {
            return;
        }

        if (time() > static::getService()->getLastSystemCleanupTime() + static::CLEANUP_PERIOD) {
            static::getQueue()->enqueue('global-utility', new ObsoleteTaskDeleter());
            static::getQueue()->enqueue('global-utility', new ObsoleteLogsRemover());
            static::getService()->setLastSystemCleanupTime(time());
        }
    }

    protected static function canHandle()
    {
        return true;
    }

    /**
     * @return ConfigService
     */
    protected static function getService()
    {
        return ServiceRegister::getService(Configuration::CLASS_NAME);
    }

    /**
     * @return QueueService
     */
    protected static function getQueue()
    {
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }
}