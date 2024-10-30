<?php


namespace ChannelEngine\BusinessLogic\Notifications\Traits;

use ChannelEngine\BusinessLogic\Notifications\Contracts\NotificationService;
use ChannelEngine\BusinessLogic\Notifications\Entities\Notification;
use ChannelEngine\BusinessLogic\TransactionLog\Entities\TransactionLog;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Trait NotificationCreator
 *
 * @package ChannelEngine\BusinessLogic\Notifications\Traits
 */
trait NotificationCreator
{
    /**
     * Creates task summary.
     *
     * @param TransactionLog $log
     * @param string $message
     * @param string $context
     */
    public function addTaskSummary(TransactionLog $log, $message, $context)
    {
        $notifications = $this->getNotificationService()->find(['transactionLogId' => $log->getId()]);
        $notification = !empty($notifications[0]) ? $notifications[0] : null;

        if ($notification) {
            $notification->setNotificationContext($context);
            $this->updateNotificationContext($notification);

            return;
        }

        $this->createNotification($log, $message, $context);
    }

    /**
     * @param TransactionLog $log
     * @param string $message
     * @param $context
     */
    protected function createNotification(TransactionLog $log, $message, $context)
    {
        $notification = new Notification();

        $notification->setMessage($message);
        $notification->setTransactionLogId($log->getId());
        $notification->setNotificationContext($context);
        $notification->setContext($log->getContext());

        $this->getNotificationService()->create($notification);
    }

    /**
     * @param Notification $notification
     */
    protected function updateNotificationContext(Notification $notification)
    {
        $this->getNotificationService()->update($notification);
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return ServiceRegister::getService(NotificationService::class);
    }
}