<?php

namespace ChannelEngine\BusinessLogic\API\Authorization\Http;

use ChannelEngine\BusinessLogic\API\Authorization\DTO\AccountInfo;
use ChannelEngine\BusinessLogic\API\Http\Authorized\AuthorizedProxy;
use ChannelEngine\BusinessLogic\API\Http\DTO\HttpRequest;

/**
 * Class Proxy
 *
 * @package ChannelEngine\BusinessLogic\API\Authorization\Http
 */
class Proxy extends AuthorizedProxy
{
    /**
     * Retrieves account info from ChannelEngine API.
     *
     * @return AccountInfo
     */
    public function getAccountInfo()
    {
        $response = $this->get(new HttpRequest('settings'));

        return AccountInfo::fromArray($response->decodeBodyToArray());
    }
}