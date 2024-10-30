<?php


namespace ChannelEngine\BusinessLogic;

use ChannelEngine\BusinessLogic\API\Orders\Http\Proxy;
use ChannelEngine\BusinessLogic\Authorization\Contracts\AuthorizationService;
use ChannelEngine\BusinessLogic\Http\SystemInfoHttpClient;
use ChannelEngine\BusinessLogic\Notifications\Contracts\NotificationService;
use ChannelEngine\BusinessLogic\Orders\ChannelSupport\OrdersChannelSupportCache;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductEventsBufferService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\DetailsService;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogService;
use ChannelEngine\BusinessLogic\TransactionLog\Listeners\AbortedListener;
use ChannelEngine\BusinessLogic\TransactionLog\Listeners\CreateListener;
use ChannelEngine\BusinessLogic\TransactionLog\Listeners\FailedListener;
use ChannelEngine\BusinessLogic\TransactionLog\Listeners\FinishedListener;
use ChannelEngine\BusinessLogic\TransactionLog\Listeners\LoadListener;
use ChannelEngine\BusinessLogic\TransactionLog\Listeners\UpdateListener;
use ChannelEngine\Infrastructure\BootstrapComponent as BaseBootstrapComponent;
use ChannelEngine\Infrastructure\Http\CurlHttpClient;
use ChannelEngine\Infrastructure\Http\HttpClient;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemAbortedEvent;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemEnqueuedEvent;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemFailedEvent;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemFinishedEvent;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemRequeuedEvent;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemStartedEvent;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;

/**
 * Class BootstrapComponent
 *
 * @package ChannelEngine\BusinessLogic
 */
class BootstrapComponent extends BaseBootstrapComponent
{

    public static function init()
    {
        parent::init();

        static::initServices();
        static::initProxies();
    }

    /**
     * Initializes proxies.
     */
    protected static function initProxies()
    {
        ServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () {
                return new SystemInfoHttpClient(new CurlHttpClient());
            }
        );

        ServiceRegister::registerService(
            Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);
                $authInfo = $authService->getAuthInfo();

                return new Proxy($httpClient, $authInfo->getAccountName(), $authInfo->getApiKey());
            }
        );

        ServiceRegister::registerService(
            API\Products\Http\Proxy::class,
            static function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);
                $authInfo = $authService->getAuthInfo();

                return new API\Products\Http\Proxy($httpClient, $authInfo->getAccountName(), $authInfo->getApiKey());
            }
        );

        ServiceRegister::registerService(
            API\Authorization\Http\Proxy::class,
            static function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);
                $authInfo = $authService->getAuthInfo();

                return new API\Authorization\Http\Proxy($httpClient, $authInfo->getAccountName(), $authInfo->getApiKey());
            }
        );

        ServiceRegister::registerService(
            API\Cancellation\Http\Proxy::class,
            static function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);
                $authInfo = $authService->getAuthInfo();

                return new API\Cancellation\Http\Proxy($httpClient, $authInfo->getAccountName(), $authInfo->getApiKey());
            }
        );

        ServiceRegister::registerService(
            API\Shipments\Http\Proxy::class,
            static function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);
                $authInfo = $authService->getAuthInfo();

                return new API\Shipments\Http\Proxy($httpClient, $authInfo->getAccountName(), $authInfo->getApiKey());
            }
        );

        ServiceRegister::registerService(
            API\Webhooks\Proxy::class,
            static function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);
                $authInfo = $authService->getAuthInfo();

                return new API\Webhooks\Proxy($httpClient, $authInfo->getAccountName(), $authInfo->getApiKey());
            }
        );

        ServiceRegister::registerService(
            API\Returns\Http\Proxy::class,
            static function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);
                $authInfo = $authService->getAuthInfo();

                return new API\Returns\Http\Proxy($httpClient, $authInfo->getAccountName(), $authInfo->getApiKey());
            }
        );
    }

    /**
     * @inheritDoc
     */
    protected static function initServices()
    {
        parent::initServices();

        ServiceRegister::registerService(
            AuthorizationService::CLASS_NAME,
            static function () {
                return new Authorization\AuthorizationService();
            }
        );

        ServiceRegister::registerService(ProductsSyncConfigService::class, static function () {
            return new Products\ProductsSyncConfigService();
        });

        ServiceRegister::registerService(ProductEventsBufferService::class, static function () {
            return new Products\ProductEventsBufferService();
        });

        ServiceRegister::registerService(
            OrdersChannelSupportCache::CLASS_NAME,
            static function () {
                return new OrdersChannelSupportCache();
            }
        );

        ServiceRegister::registerService(
            OrdersConfigurationService::CLASS_NAME,
            static function () {
                return new OrdersConfigurationService();
            }
        );

        ServiceRegister::registerService(
            TransactionLogService::class,
            static function () {
                return new TransactionLog\TransactionLogService();
            }
        );

        ServiceRegister::registerService(
            DetailsService::class,
            static function () {
                return new TransactionLog\DetailsService();
            }
        );

        ServiceRegister::registerService(
            NotificationService::class,
            static function () {
                return new Notifications\NotificationService();
            }
        );

        ServiceRegister::registerService(
            QueueItemStateTransitionEventBus::class,
            static function () {
                return QueueItemStateTransitionEventBus::getInstance();
            }
        );
    }

    protected static function initEvents()
    {
        parent::initEvents();

        /** @var QueueItemStateTransitionEventBus $queueBus */
        $queueBus = ServiceRegister::getService(QueueItemStateTransitionEventBus::CLASS_NAME);

        $queueBus->when(QueueItemEnqueuedEvent::class, [new CreateListener(), 'handle']);
        $queueBus->when(QueueItemRequeuedEvent::class, [new CreateListener(), 'handle']);
        $queueBus->when(QueueItemStartedEvent::class, [new LoadListener(), 'handle']);
        $queueBus->when(QueueItemStartedEvent::class, [new UpdateListener(), 'handle']);
        $queueBus->when(QueueItemFinishedEvent::class, [new FinishedListener(), 'handle']);
        $queueBus->when(QueueItemFailedEvent::class, [new FailedListener(), 'handle']);
        $queueBus->when(QueueItemAbortedEvent::class, [new AbortedListener(), 'handle']);
    }
}
