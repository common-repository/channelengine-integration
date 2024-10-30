<?php

namespace ChannelEngine\Infrastructure\TaskExecution\TaskEvents\Listeners;

use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;
use RuntimeException;

/**
 * Class OnReportAlive
 *
 * @package ChannelEngine\Infrastructure\TaskExecution\TaskEvents\Listeners
 */
class OnReportAlive
{
    /**
     * Handles report alive event.
     *
     * @param QueueItem $queueItem
     *
     * @throws QueueStorageUnavailableException
     */
    public static function handle(QueueItem $queueItem)
    {
        $queue = static::getQueue();
        $queue->keepAlive($queueItem);
        if ($queueItem->getParentId() === null) {
            return;
        }

        $parent = $queue->find($queueItem->getParentId());

        if ($parent === null) {
            throw new RuntimeException("Parent not available.");
        }

        $queue->keepAlive($parent);
    }

    /**
     * Provides queue service.
     *
     * @return QueueService
     */
    private static function getQueue()
    {
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }
}