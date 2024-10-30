<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Listeners;

use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogAware;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogService;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;

/**
 * Class Listener
 *
 * @package ChannelEngine\BusinessLogic\TransactionLog\Listeners
 */
abstract class Listener
{
    /**
     * Manages transaction log on state change.
     *
     * @param BaseQueueItemEvent $event
     *
     * @throws QueueItemDeserializationException
     */
    public function handle(BaseQueueItemEvent $event)
    {
        if (!$this->canHandle($event)) {
            return;
        }

        $this->doHandle($event);
    }

    /**
     * Handles the event.
     *
     * @param BaseQueueItemEvent $event
     */
    abstract protected function doHandle(BaseQueueItemEvent $event);

    /**
     * Check if event should be handled.
     *
     * @param BaseQueueItemEvent $event
     * @return bool
     *
     * @throws QueueItemDeserializationException
     */
    protected function canHandle(BaseQueueItemEvent $event)
    {
        $task = $event->getQueueItem()->getTask();
        if ($task === null) {
            return false;
        }

        return $task instanceof TransactionLogAware;
    }

    /**
     * Provides transaction log service.
     *
     * @return TransactionLogService
     */
    protected function getService()
    {
        return ServiceRegister::getService(TransactionLogService::class);
    }
}