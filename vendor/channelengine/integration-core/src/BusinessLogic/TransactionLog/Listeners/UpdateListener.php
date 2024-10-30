<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Listeners;

use ChannelEngine\BusinessLogic\TransactionLog\Entities\TransactionLog;
use ChannelEngine\BusinessLogic\TransactionLog\Traits\TransactionLogAware;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;
use ChannelEngine\Infrastructure\Utility\TimeProvider;

/**
 * Class UpdateListener
 *
 * @package ChannelEngine\BusinessLogic\TransactionLog\Listeners
 */
class UpdateListener extends Listener
{
    /**
     * @var TransactionLog
     */
    protected $transactionLog;
    /**
     * @var BaseQueueItemEvent
     */
    protected $event;
    /**
     * @var QueueItem
     */
    protected $queueItem;

    /**
     * @inheritDoc
     */
    protected function doHandle(BaseQueueItemEvent $event)
    {
        $this->init($event);

        $this->transactionLog->setStatus($this->queueItem->getStatus());

        $this->save();
    }

    /**
     * @param BaseQueueItemEvent $event
     *
     * @return void
     *
     * @throws QueueItemDeserializationException
     */
    protected function init(BaseQueueItemEvent $event)
    {
        $this->event = $event;
        $this->queueItem = $this->extractQueueItem();

        /** @var TransactionLogAware $task */
        $task = $this->queueItem->getTask();
        $this->transactionLog = $task->getTransactionLog();
    }

    /**
     * @inheritdoc
     */
    protected function canHandle(BaseQueueItemEvent $event)
    {
        if (!parent::canHandle($event)) {
            return false;
        }

        $queueItem = $event->getQueueItem();

        /** @var TransactionLogAware $task */
        $task = $queueItem->getTask();
        if (!$task || !$task->getTransactionLog()) {
            return false;
        }

        if ($queueItem->getParentId() && !$this->getQueue()->find($queueItem->getParentId())) {
            return false;
        }

        return true;
    }

    protected function save()
    {
        $this->getService()->update($this->transactionLog);
    }

    /**
     * @return QueueItem
     */
    protected function extractQueueItem()
    {
        $queueItem = $this->event->getQueueItem();
        if ($queueItem->getParentId() !== null) {
            $queueItem = $this->getQueue()->find($queueItem->getParentId());
        }

        return $queueItem;
    }

    /**
     * @return QueueService
     */
    private function getQueue()
    {
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }

    /**
     * @return TimeProvider
     */
    protected function getTimeProvider()
    {
        return ServiceRegister::getService(TimeProvider::class);
    }
}