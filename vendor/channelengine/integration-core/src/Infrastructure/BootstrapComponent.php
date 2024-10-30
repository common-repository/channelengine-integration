<?php

namespace ChannelEngine\Infrastructure;

use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\Http\CurlHttpClient;
use ChannelEngine\Infrastructure\Http\HttpClient;
use ChannelEngine\Infrastructure\TaskExecution\AsyncProcessStarterService;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\TaskRunnerManager;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;
use ChannelEngine\Infrastructure\TaskExecution\RunnerStatusStorage;
use ChannelEngine\Infrastructure\TaskExecution\TaskRunner;
use ChannelEngine\Infrastructure\TaskExecution\TaskRunnerWakeupService;
use ChannelEngine\Infrastructure\Utility\Events\EventBus;
use ChannelEngine\Infrastructure\Utility\GuidProvider;
use ChannelEngine\Infrastructure\Utility\TimeProvider;

/**
 * Class BootstrapComponent.
 *
 * @package ChannelEngine\Infrastructure
 */
class BootstrapComponent
{
    /**
     * Initializes infrastructure components.
     */
    public static function init()
    {
        static::initServices();
        static::initRepositories();
        static::initEvents();
    }

    /**
     * Initializes services and utilities.
     */
    protected static function initServices()
    {
        ServiceRegister::registerService(
            ConfigurationManager::CLASS_NAME,
            function () {
                return ConfigurationManager::getInstance();
            }
        );
        ServiceRegister::registerService(
            TimeProvider::CLASS_NAME,
            function () {
                return TimeProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            GuidProvider::CLASS_NAME,
            function () {
                return GuidProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            EventBus::CLASS_NAME,
            function () {
                return EventBus::getInstance();
            }
        );
        ServiceRegister::registerService(
            AsyncProcessService::CLASS_NAME,
            function () {
                return AsyncProcessStarterService::getInstance();
            }
        );
        ServiceRegister::registerService(
            QueueService::CLASS_NAME,
            function () {
                return new QueueService();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerWakeup::CLASS_NAME,
            function () {
                return new TaskRunnerWakeupService();
            }
        );
        ServiceRegister::registerService(
            TaskRunner::CLASS_NAME,
            function () {
                return new TaskRunner();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerStatusStorage::CLASS_NAME,
            function () {
                return new RunnerStatusStorage();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerManager::CLASS_NAME,
            function () {
                return new TaskExecution\TaskRunnerManager();
            }
        );
        ServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () {
                return new CurlHttpClient();
            }
        );
    }

    /**
     * Initializes repositories.
     */
    protected static function initRepositories()
    {
    }

    /**
     * Initializes events.
     */
    protected static function initEvents()
    {
    }
}
