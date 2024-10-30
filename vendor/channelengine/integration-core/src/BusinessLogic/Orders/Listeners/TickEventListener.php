<?php


namespace ChannelEngine\BusinessLogic\Orders\Listeners;

use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Orders\Handlers\TickEventHandler;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use ChannelEngine\Infrastructure\Utility\TimeProvider;
use DateTime;

/**
 * Class TickEventListener
 *
 * @package ChannelEngine\BusinessLogic\Orders\Listeners
 */
class TickEventListener
{
    /**
     * Minimum interval between two consecutive checks.
     */
    const MIN_INTERVAL = 7200;

    /**
     * Listens to tick event.
     *
     * @throws RepositoryNotRegisteredException
     * @throws QueueStorageUnavailableException
     */
    public static function handle()
    {
        if (!static::canHandle()) {
            return;
        }

        static::doHandle();
    }

    /**
     * Checks if event can be handled.
     * Should be implemented in integration.
     *
     * @return bool
     */
    protected static function canHandle()
    {
        return true;
    }

    /**
     * @throws QueueStorageUnavailableException
     * @throws RepositoryNotRegisteredException
     */
    protected static function doHandle()
    {
        $ordersConfigService = static::getOrdersConfigService();

        $checkTime = $ordersConfigService->getLastOrderSyncCheckTime();
        $nextSyncTime = $checkTime->modify("+ " . static::MIN_INTERVAL . " seconds");
        $now = static::getTimeProvider()->getCurrentLocalTime();

        if ($nextSyncTime <= $now) {
            $handler = new TickEventHandler();
            $handler->handleOrders();
            $ordersConfigService->setLastOrderSyncCheckTime(new DateTime());
        }
    }

    /**
     * Retrieves an instance of OrdersConfigurationService.
     *
     * @return OrdersConfigurationService
     */
    protected static function getOrdersConfigService()
    {
        return ServiceRegister::getService(OrdersConfigurationService::class);
    }

    /**
     * @return TimeProvider
     */
    protected static function getTimeProvider()
    {
        return ServiceRegister::getService(TimeProvider::CLASS_NAME);
    }
}