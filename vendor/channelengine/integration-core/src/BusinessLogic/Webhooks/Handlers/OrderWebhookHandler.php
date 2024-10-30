<?php

namespace ChannelEngine\BusinessLogic\Webhooks\Handlers;

use ChannelEngine\BusinessLogic\InitialSync\OrderSync;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Webhooks\DTO\Webhook;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use DateTime;

/**
 * Class OrderWebhookHandler
 *
 * @package ChannelEngine\BusinessLogic\Webhooks\Handlers
 */
class OrderWebhookHandler extends WebhooksHandler
{
    /**
     * @return void
     *
     * @throws RepositoryNotRegisteredException
     * @throws QueueStorageUnavailableException
     */
    protected function doHandle()
    {
        $this->getQueueService()->enqueue(
            'order-sync',
            new OrderSync(),
            ConfigurationManager::getInstance()->getContext()
        );
        $this->getOrdersConfigService()->setLastOrderSyncCheckTime(new DateTime());
    }

    /**
     * @param Webhook $webhook
     *
     * @return bool
     */
    protected function isWebhookValid(Webhook $webhook)
    {
        return parent::isWebhookValid($webhook) && $webhook->getEvent() === 'orders';
    }

    /**
     * @return OrdersConfigurationService
     */
    protected function getOrdersConfigService()
    {
        if ($this->ordersConfigService === null) {
            $this->ordersConfigService = ServiceRegister::getService(OrdersConfigurationService::class);
        }

        return $this->ordersConfigService;
    }
}