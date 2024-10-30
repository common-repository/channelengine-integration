<?php


namespace ChannelEngine\BusinessLogic\Products\Handlers;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductEventsBufferService;
use ChannelEngine\BusinessLogic\Products\Domain\ProductUpsert;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class ProductUpsertEventHandler
 *
 * @package ChannelEngine\BusinessLogic\Products\Handlers
 */
class ProductUpsertEventHandler
{
    /**
     * Handles product upsert event.
     *
     * @param ProductUpsert $upsert
     */
    public function handle(ProductUpsert $upsert)
    {
        try {
            if (ConfigurationManager::getInstance()->getConfigValue('syncProducts', 1)) {
                $this->getService()->recordUpsert($upsert);
            }
        } catch (QueryFilterInvalidParamException $exception) {
            Logger::logError($exception->getMessage());
        }
    }

    /**
     * Provides service.
     *
     * @return ProductEventsBufferService
     */
    protected function getService()
    {
        return ServiceRegister::getService(ProductEventsBufferService::class);
    }
}