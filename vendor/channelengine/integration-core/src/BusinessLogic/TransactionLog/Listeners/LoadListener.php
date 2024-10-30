<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Listeners;

use ChannelEngine\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;

/**
 * Class LoadListener
 *
 * @package ChannelEngine\BusinessLogic\TransactionLog\Listeners
 */
class LoadListener extends Listener
{
    /**
     * @inheritDoc
     */
    protected function doHandle(BaseQueueItemEvent $event)
    {
        $this->getService()->load($event->getQueueItem());
    }
}