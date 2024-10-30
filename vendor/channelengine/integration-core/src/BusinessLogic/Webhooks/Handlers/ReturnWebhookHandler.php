<?php

namespace ChannelEngine\BusinessLogic\Webhooks\Handlers;

use ChannelEngine\BusinessLogic\Returns\Tasks\ReturnSync;
use ChannelEngine\BusinessLogic\Webhooks\DTO\Webhook;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

/**
 * Class ReturnWebhookHandler
 *
 * @package ChannelEngine\BusinessLogic\Webhooks\Handlers
 */
class ReturnWebhookHandler extends WebhooksHandler
{
    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     */
    protected function doHandle()
    {
        $this->getQueueService()->enqueue(
            'returns-sync',
            new ReturnSync(),
            ConfigurationManager::getInstance()->getContext()
        );
    }

    /**
     * @param Webhook $webhook
     *
     * @return bool
     */
    protected function isWebhookValid(Webhook $webhook)
    {
        return parent::isWebhookValid($webhook) && $webhook->getEvent() === 'returns';
    }
}