<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Listeners;

use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogAware;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogService;
use ChannelEngine\BusinessLogic\TransactionLog\Entities\TransactionLog;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemFailedEvent;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;
use ChannelEngine\Infrastructure\Utility\TimeProvider;

/**
 * Class FailedListener
 *
 * @package ChannelEngine\BusinessLogic\TransactionLog\Listeners
 */
class FailedListener extends Listener
{
    /**
     * @var TransactionLogService
     */
    protected $transactionLogService;
    /**
     * @var TransactionLog
     */
    protected $transactionLog;
    /**
     * @var QueueItemFailedEvent
     */
    protected $event;
    /**
     * @var QueueItem
     */
    protected $queueItem;

    /**
     * @inheritDoc
     */
    public function handle(BaseQueueItemEvent $event)
    {
        if (!$this->canHandle($event)) {
            return;
        }

        $this->init($event);
        $this->doHandle($event);
    }

    /**
     * @throws QueueItemDeserializationException
     */
    protected function init(BaseQueueItemEvent $event)
    {
        $this->event = $event;
        $this->initQueueItem();
        $this->initTransactionLog();
    }

    protected function initQueueItem()
    {
        $this->queueItem = $this->event->getQueueItem();
        if ($this->queueItem->getParentId() !== null) {
            $this->queueItem = $this->getQueue()->find($this->queueItem->getParentId());
        }
    }

    /**
     * @return void
     *
     * @throws QueueItemDeserializationException
     */
    protected function initTransactionLog()
    {
        if (!$this->queueItem) {
            return;
        }

        $this->transactionLogService = $this->getService();

        /** @var TransactionLogAware $task */
        $task = $this->queueItem->getTask();
        if ($task && $task->getTransactionLog()) {
            $this->transactionLog = $task->getTransactionLog();

            return;
        }

        $transactionLogs = $this->transactionLogService->find(['executionId' => $this->queueItem->getId()], 0, 1);
        if (!empty($transactionLogs)) {
            $this->transactionLog = array_pop($transactionLogs);

        }
    }

    /**
     * @inheritDoc
     */
    protected function doHandle(BaseQueueItemEvent $event)
    {
        if (!$this->transactionLog) {
            return;
        }

        Logger::logWarning('Task failure detected', 'Core', [
            'QueueItemId' => $event->getQueueItem()->getId(),
            'QueueItemFailureDescription' => $event->getQueueItem()->getFailureDescription()
        ]);
        $this->transactionLog->setStatus($this->queueItem->getStatus());

        $this->transactionLog->setCompletedTime($this->getTimeProvider()->getCurrentLocalTime());
        if ($failTimestamp = $this->queueItem->getFailTimestamp()) {
            $this->transactionLog->setCompletedTime($this->getTimeProvider()->getDateTime($failTimestamp));
        }

        $this->transactionLogService->update($this->transactionLog);
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