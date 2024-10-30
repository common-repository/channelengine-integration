<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Listeners;

use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemFinishedEvent;

/**
 * Class FinishedListener
 *
 * @package ChannelEngine\BusinessLogic\TransactionLog\Listeners
 */
class FinishedListener extends UpdateListener
{
    /**
     * @var QueueItemFinishedEvent
     */
    protected $event;

    /**
     * @inheritdoc
     */
    protected function save()
    {
        $this->transactionLog->setCompletedTime($this->getTimeProvider()->getCurrentLocalTime());
        if ($finishTimestamp = $this->queueItem->getFinishTimestamp()) {
            $this->transactionLog->setCompletedTime(
                $this->getTimeProvider()->getDateTime($finishTimestamp)
            );
        }

        parent::save();
    }
}