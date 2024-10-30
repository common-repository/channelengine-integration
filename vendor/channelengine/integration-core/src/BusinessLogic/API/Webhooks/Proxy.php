<?php

namespace ChannelEngine\BusinessLogic\API\Webhooks;

use ChannelEngine\BusinessLogic\API\Http\Authorized\AuthorizedProxy;
use ChannelEngine\BusinessLogic\API\Http\DTO\HttpRequest;
use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\BusinessLogic\API\Webhooks\DTO\Webhook;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class Proxy
 *
 * @package ChannelEngine\BusinessLogic\API\Webhooks
 */
class Proxy extends AuthorizedProxy
{
	/**
	 * Creates a webhook.
	 *
	 * @param Webhook $webhook
	 *
	 * @throws RequestNotSuccessfulException
	 * @throws HttpCommunicationException
	 * @throws HttpRequestException
	 * @throws QueryFilterInvalidParamException
	 */
    public function create(Webhook $webhook)
    {
        $request = new HttpRequest('webhooks', $webhook->toArray());
        $this->post($request);
    }

	/**
	 * Deletes webhook by name.
	 *
	 * @param $name
	 *
	 * @throws RequestNotSuccessfulException
	 * @throws HttpCommunicationException
	 * @throws HttpRequestException
	 * @throws QueryFilterInvalidParamException
	 */
    public function deleteWebhook($name)
    {
        $request = new HttpRequest("webhooks/$name");
        $this->delete($request);
    }
}