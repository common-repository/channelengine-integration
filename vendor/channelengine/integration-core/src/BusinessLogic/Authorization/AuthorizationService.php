<?php

namespace ChannelEngine\BusinessLogic\Authorization;

use ChannelEngine\BusinessLogic\API\Authorization\Http\Proxy;
use ChannelEngine\BusinessLogic\Authorization\DTO\AuthInfo;
use ChannelEngine\BusinessLogic\Authorization\Exceptions\CurrencyMismatchException;
use ChannelEngine\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\Http\HttpClient;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class AuthorizationService
 *
 * @package ChannelEngine\BusinessLogic\Authorization
 */
class AuthorizationService implements Contracts\AuthorizationService
{
    /**
     * Retrieves auth info object for the current user
     *
     * @return AuthInfo Instance of auth info object.
     *
     * @throws QueryFilterInvalidParamException
     * @throws FailedToRetrieveAuthInfoException
     */
    public function getAuthInfo()
    {
        $savedInfo = $this->getConfigurationManager()->getConfigValue('authInfo', array());

        if (empty($savedInfo)) {
            throw new FailedToRetrieveAuthInfoException('Failed to retrieve auth info.');
        }

        return AuthInfo::fromArray($savedInfo);
    }

    /**
     * Sets auth info for the current user.
     *
     * @param AuthInfo $authInfo Auth info object instance.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setAuthInfo($authInfo = null)
    {
        $this->getConfigurationManager()->saveConfigValue('authInfo', $authInfo ? $authInfo->toArray() : null);
    }

    /**
     * Validates account info.
     *
     * @param $apiKey
     * @param $accountName
     * @param $currency
     *
     * @throws CurrencyMismatchException
     */
    public function validateAccountInfo($apiKey, $accountName, $currency)
    {
        $authProxy = new Proxy(ServiceRegister::getService(HttpClient::class), $accountName, $apiKey);
        $accountInfo = $authProxy->getAccountInfo();

        if (strtoupper($accountInfo->getCurrencyCode()) !== strtoupper($currency)) {
            throw new CurrencyMismatchException(
                'Currency mismatch detected. Please make sure that store currency matches ChannelEngine.');
        }
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager
     */
    protected function getConfigurationManager()
    {
        return ServiceRegister::getService(ConfigurationManager::CLASS_NAME);
    }
}