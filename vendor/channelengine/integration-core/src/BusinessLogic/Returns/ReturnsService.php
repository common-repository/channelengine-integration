<?php

namespace ChannelEngine\BusinessLogic\Returns;

use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\BusinessLogic\API\Returns\DTO\MerchantReturnRequest;
use ChannelEngine\BusinessLogic\API\Returns\DTO\MerchantReturnUpdate;
use ChannelEngine\BusinessLogic\API\Returns\Http\Proxy;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class ReturnsService
 *
 * @package ChannelEngine\BusinessLogic\Returns
 */
abstract class ReturnsService implements Contracts\ReturnsService
{
    /**
     * @inheritDoc
     *
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws RequestNotSuccessfulException
     */
    public function createOnChannelEngine(MerchantReturnRequest $request)
    {
        $this->getProxy()->create($request);
    }

    /**
     * @inheritDoc
     *
     * @throws RequestNotSuccessfulException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function update(MerchantReturnUpdate $update)
    {
        $this->getProxy()->update($update);
    }

    /**
     * @return Proxy
     */
    protected function getProxy()
    {
        return ServiceRegister::getService(Proxy::class);
    }
}
