<?php

namespace ChannelEngine\BusinessLogic\Products\Handlers;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductEventsBufferService;
use ChannelEngine\BusinessLogic\Products\Domain\ProductDeleted;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class ProductDeletedEventHandler
 *
 * @package ChannelEngine\BusinessLogic\Products\Handlers
 */
class ProductDeletedEventHandler
{
    /**
     * Handles product deleted event.
     *
     * @param ProductDeleted $deleted
     */
    public function handle(ProductDeleted $deleted)
    {
        try {
            if (ConfigurationManager::getInstance()->getConfigValue('syncProducts', 1)) {
                $this->getService()->recordDeleted($deleted);
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