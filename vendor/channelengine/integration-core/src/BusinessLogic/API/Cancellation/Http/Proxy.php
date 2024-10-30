<?php

namespace ChannelEngine\BusinessLogic\API\Cancellation\Http;

use ChannelEngine\BusinessLogic\API\Cancellation\DTO\Cancellation;
use ChannelEngine\BusinessLogic\API\Http\Authorized\AuthorizedProxy;
use ChannelEngine\BusinessLogic\API\Http\DTO\HttpRequest;
use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class Proxy
 *
 * @package ChannelEngine\BusinessLogic\API\Cancellation\Http
 */
class Proxy extends AuthorizedProxy
{
    /**
     * Submits order cancellation.
     *
     * @param Cancellation $cancellation
     *
     * @throws RequestNotSuccessfulException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function submit(Cancellation $cancellation)
    {
        $request = new HttpRequest('cancellations', $cancellation->toArray());
        $this->post($request);
    }
}