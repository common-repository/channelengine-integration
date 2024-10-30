<?php

namespace ChannelEngine\BusinessLogic\Products\Contracts;

use ChannelEngine\BusinessLogic\Products\Domain\ProductDeleted;
use ChannelEngine\BusinessLogic\Products\Domain\ProductUpsert;
use ChannelEngine\BusinessLogic\Products\Entities\ProductEvent;

/**
 * Interface ProductEventsBufferService
 *
 * @package ChannelEngine\BusinessLogic\Products\Contracts
 */
interface ProductEventsBufferService
{
    /**
     * Records deleted product event.
     *
     * @param ProductDeleted $deleted
     *
     * @return void
     */
    public function recordDeleted(ProductDeleted $deleted);

    /**
     * Records product upsert event
     *
     * @param ProductUpsert $upsert
     *
     * @return void
     */
    public function recordUpsert(ProductUpsert  $upsert);

    /**
     * Gets events with given type.
     *
     * @param string $type
     * @param int $offset
     * @param int $limit
     *
     * @return ProductEvent[]
     */
    public function get($type, $offset, $limit);

    /**
     * Deletes product events.
     *
     * @param ProductEvent[] $events
     *
     * @return void
     */
    public function delete(array $events);

    /**
     * Retrieves last time events from buffer were read.
     *
     * @return int
     */
    public function getLastReadTime();

    /**
     * Updates last time events from buffer were read.
     *
     * @param int $lastReadTime
     *
     * @return void
     */
    public function updateLastReadTime($lastReadTime);
}