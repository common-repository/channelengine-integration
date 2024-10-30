<?php

namespace ChannelEngine\BusinessLogic\Notifications\Contracts;

use ChannelEngine\BusinessLogic\Notifications\Entities\Notification;

/**
 * Interface NotificationService
 *
 * @package ChannelEngine\BusinessLogic\Notifications\Contracts
 */
interface NotificationService
{
    /**
     * Creates notification
     *
     * @param Notification $notification
     *
     * @return void
     */
    public function create(Notification $notification);

    /**
     * Updates notification.
     *
     * @param Notification $notification
     *
     * @return void
     */
    public function update(Notification $notification);

    /**
     * Deletes notification.
     *
     * @param Notification $notification
     *
     * @return void
     */
    public function delete(Notification $notification);

    /**
     * Provides notification identified by id.
     *
     * @param $id
     *
     * @return Notification | null
     */
    public function get($id);

	/**
	 * Provides notifications identified by query.
	 *
	 * @param array $query
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return Notification[]
	 */
    public function find(array $query, $offset = 0, $limit = 1000);

    /**
     * Provides current count of unread notifications.
     *
     * @param array $query
     *
     * @return int
     */
    public function countNotRead(array $query = []);
}