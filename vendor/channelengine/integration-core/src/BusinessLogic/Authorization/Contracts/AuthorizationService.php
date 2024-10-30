<?php


namespace ChannelEngine\BusinessLogic\Authorization\Contracts;

use ChannelEngine\BusinessLogic\Authorization\DTO\AuthInfo;
use ChannelEngine\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Interface AuthorizationService
 *
 * @package ChannelEngine\BusinessLogic\Authorization\Contracts
 */
interface AuthorizationService
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves auth info object for the current user
     *
     * @return AuthInfo Instance of auth info object.
     *
     * @throws QueryFilterInvalidParamException
     * @throws FailedToRetrieveAuthInfoException
     */
    public function getAuthInfo();

    /**
     * Sets auth info for the current user.
     *
     * @param AuthInfo $authInfo Auth info object instance.
     */
    public function setAuthInfo($authInfo = null);
}