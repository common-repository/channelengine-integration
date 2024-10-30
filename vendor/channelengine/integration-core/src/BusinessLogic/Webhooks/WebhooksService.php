<?php

namespace ChannelEngine\BusinessLogic\Webhooks;

use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\BusinessLogic\API\Webhooks\DTO\Webhook;
use ChannelEngine\BusinessLogic\API\Webhooks\Proxy;
use ChannelEngine\BusinessLogic\Webhooks\Contracts\WebhooksService as BaseService;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\Exceptions\BaseException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\Utility\GuidProvider;

/**
 * Class WebhooksService
 *
 * @package ChannelEngine\BusinessLogic\Webhooks
 */
abstract class WebhooksService implements BaseService
{
    /**
     * @inheritDoc
     *
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws RequestNotSuccessfulException
     */
    public function create()
    {
        try {
            $this->delete();
        } catch (BaseException $e) {
            // Before webhook creation, we try to delete a webhook
            // with the same name on ChannelEngine. If a webhook with the same name does not exist,
            // there is no need for any actions.
        }

        $webhook = new Webhook();
        $webhook->setEvents($this->getEvents());
        $webhook->setName($this->getName());
        $webhook->setUrl($this->getWebhookUrl());
        $webhook->setIsActive(true);

        $this->getProxy()->create($webhook);
    }

    /**
     * @inheritDoc
     *
     * @throws RequestNotSuccessfulException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function delete()
    {
        $this->getProxy()->deleteWebhook($this->getName());
    }

    /**
     * Creates webhook token.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function createWebhookToken()
    {
        $token = $this->getGuidProvider()->generateGuid();

        ConfigurationManager::getInstance()->saveConfigValue('CHANNELENGINE_WEBHOOK_TOKEN', $token);
    }

    /**
     * Retrieves webhook token.
     *
     * @return string
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getWebhookToken()
    {
        return ConfigurationManager::getInstance()->getConfigValue('CHANNELENGINE_WEBHOOK_TOKEN', '');
    }

    /**
     * Retrieves webhook url.
     *
     * @return string
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getWebhookUrl()
    {
        $url = $this->getUrl();
        $parsedUrl = parse_url($url);
        $separator = isset($parsedUrl['query']) ? '&' : '?';

        return $url . $separator . http_build_query(['token' => $this->getWebhookToken()]);
    }

    /**
     * Provides list of available events.
     *
     * @return array
     */
    abstract protected function getEvents();

    /**
     * Provides webhook name. This name will be used to identify webhook.
     *
     * @retrun string
     */
    abstract protected function getName();

    /**
     * Webhook handling url.
     *
     * @retrun string
     */
    abstract protected function getUrl();

    /**
     * Provides proxy.
     *
     * @return Proxy
     */
    protected function getProxy()
    {
        return ServiceRegister::getService(Proxy::class);
    }

    /**
     * Provides GuidProvider.
     *
     * @return GuidProvider
     */
    protected function getGuidProvider()
    {
        return ServiceRegister::getService(GuidProvider::class);
    }
}