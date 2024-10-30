<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Listeners;

use ChannelEngine\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;

/**
 * Class CreateListener
 *
 * @package ChannelEngine\BusinessLogic\TransactionLog\Listeners
 */
class CreateListener extends Listener
{
    /**
     * @inheritDoc
     */
    protected function doHandle(BaseQueueItemEvent $event)
    {
        $queueItem = $event->getQueueItem();

        if ($queueItem->getParentId() !== null) {
            return;
        }

        $this->getService()->create($queueItem);
    }
}