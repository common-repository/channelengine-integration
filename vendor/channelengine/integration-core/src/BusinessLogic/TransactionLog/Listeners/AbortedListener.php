<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Listeners;

use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemAbortedEvent;

/**
 * Class AbortedListener
 *
 * @package ChannelEngine\BusinessLogic\TransactionLog\Listeners
 */
class AbortedListener extends UpdateListener
{
    /**
     * @var QueueItemAbortedEvent
     */
    protected $event;

    protected function save()
    {
        $this->transactionLog->setCompletedTime($this->getTimeProvider()->getCurrentLocalTime());
        if ($failTimestamp = $this->queueItem->getFailTimestamp()) {
            $this->transactionLog->setCompletedTime($this->getTimeProvider()->getDateTime($failTimestamp));
        }

        parent::save();
    }
}