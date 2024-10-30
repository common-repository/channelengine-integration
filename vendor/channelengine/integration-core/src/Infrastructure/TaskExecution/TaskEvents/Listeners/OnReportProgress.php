<?php

namespace ChannelEngine\Infrastructure\TaskExecution\TaskEvents\Listeners;

use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Composite\Orchestrator;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;
use RuntimeException;

/**
 * Class OnReportProgress
 *
 * @package ChannelEngine\Infrastructure\TaskExecution\TaskEvents\Listeners
 */
class OnReportProgress
{
    /**
     * Handles queue item progress change.
     *
     * @param QueueItem $queueItem
     * @param $progressBasePoints
     *
     * @throws QueueStorageUnavailableException
     * @throws QueueItemDeserializationException
     */
    public static function handle(QueueItem $queueItem, $progressBasePoints)
    {
        $queue = static::getQueueService();
        $queue->updateProgress($queueItem, $progressBasePoints);
        if ($queueItem->getParentId() === null) {
            return;
        }

        $parent = $queue->find($queueItem->getParentId());

        if ($parent === null) {
            throw new RuntimeException("Parent not available.");
        }

        /** @var Orchestrator $task */
        $task = $parent->getTask();
        if ($task === null || !($task instanceof Orchestrator)) {
            throw new RuntimeException("Failed to retrieve task.");
        }

        $task->updateSubJobProgress($queueItem->getId(), $queueItem->getProgressFormatted());
    }

    /**
     * Provides queue service.
     *
     * @return QueueService
     */
    private static function getQueueService()
    {
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }
}