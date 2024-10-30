<?php


namespace ChannelEngine\BusinessLogic\Products\Handlers;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductEventsBufferService;
use ChannelEngine\BusinessLogic\Products\Entities\ProductEvent;
use ChannelEngine\BusinessLogic\Products\Tasks\ProductsDeleteTask;
use ChannelEngine\BusinessLogic\Products\Tasks\ProductsUpsertTask;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;

/**
 * Class TickEventHandler
 *
 * @package ChannelEngine\BusinessLogic\Products\Handlers
 */
class TickEventHandler
{
    const BATCH_SIZE = 2500;
    /**
     * @var ProductEventsBufferService
     */
    protected $productEventBuffer;
    /**
     * @var QueueService
     */
    protected $queueService;

    /**
     * Handles tick event.
     *
     * @throws QueueStorageUnavailableException
     */
    public function handle()
    {
        $this->handleTickEvent(ProductEvent::DELETED);
        $this->handleTickEvent(ProductEvent::UPSERT);
    }

    /**
     * Handles tick event for event type.
     *
     * @param string $type
     *
     * @throws QueueStorageUnavailableException
     */
    protected function handleTickEvent($type)
    {
        $offset = 0;
        $events = $this->getEvents($type, $offset, static::BATCH_SIZE);

        while (!empty($events)) {
            $ids = $this->extractProductIds($events);
            $task = $this->getTask($type, $ids);
            $this->enqueueTask($task);
            $this->deleteEvents($events);
            $offset++;
            $events = $this->getEvents($type, $offset * static::BATCH_SIZE, static::BATCH_SIZE);
        }
    }

    /**
     * Retrieves sync task.
     *
     * @param $type
     * @param $ids
     *
     * @return ProductsDeleteTask|ProductsUpsertTask
     */
    protected function getTask($type, $ids)
    {
        if ($type === ProductEvent::DELETED) {
            return new ProductsDeleteTask($ids);
        }

        return new ProductsUpsertTask($ids);
    }

    /**
     * Deletes events.
     *
     * @param ProductEvent[] $events
     */
    protected function deleteEvents(array $events)
    {
        $this->getProductEventBufferService()->delete($events);
    }

    /**
     * Extracts product ids from events.
     *
     * @param ProductEvent[] $events
     */
    protected function extractProductIds(array $events)
    {
        $productIds = [];

        foreach ($events as $event) {
            $productIds[] = $event->getProductId();
        }

        return $productIds;
    }

    /**
     * Gets product events by type.
     *
     * @param string $type
     * @param int $offset
     * @param int $limit
     *
     * @return ProductEvent[]
     */
    protected function getEvents($type, $offset, $limit)
    {
        return $this->getProductEventBufferService()->get($type, $offset, $limit);
    }

    /**
     * Enqueues task.
     *
     * @param $task
     *
     * @throws QueueStorageUnavailableException
     */
    protected function enqueueTask($task)
    {
        $this->getQueueService()->enqueue('product-sync', $task, ConfigurationManager::getInstance()->getContext());
    }

    /**
     * @return ProductEventsBufferService
     */
    protected function getProductEventBufferService()
    {
        if ($this->productEventBuffer === null) {
            $this->productEventBuffer = ServiceRegister::getService(ProductEventsBufferService::class);
        }

        return $this->productEventBuffer;
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        if ($this->queueService === null) {
            $this->queueService = ServiceRegister::getService(QueueService::class);
        }

        return $this->queueService;
    }
}