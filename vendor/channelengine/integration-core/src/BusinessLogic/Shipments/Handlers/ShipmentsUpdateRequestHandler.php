<?php

namespace ChannelEngine\BusinessLogic\Shipments\Handlers;

use ChannelEngine\BusinessLogic\API\Shipments\DTO\MerchantShipmentTrackingRequest;
use ChannelEngine\BusinessLogic\API\Shipments\Http\Proxy;
use ChannelEngine\BusinessLogic\Notifications\Contracts\Context;
use ChannelEngine\BusinessLogic\Notifications\Contracts\NotificationService;
use ChannelEngine\BusinessLogic\Notifications\Entities\Notification;
use ChannelEngine\BusinessLogic\Orders\ChannelSupport\Exceptions\FailedToRetrieveOrdersChannelSupportEntityException;
use ChannelEngine\BusinessLogic\Orders\ChannelSupport\OrdersChannelSupportCache;
use ChannelEngine\BusinessLogic\Shipments\Contracts\ShipmentsService;
use ChannelEngine\BusinessLogic\Shipments\Domain\UpdateShipmentRequest;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\Exceptions\BaseException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\Utility\TimeProvider;
use DateTime;

/**
 * Class ShipmentsUpdateRequestHandler
 *
 * @package ChannelEngine\BusinessLogic\Shipments\Handlers
 */
class ShipmentsUpdateRequestHandler
{
    /**
     * @var ShipmentsService
     */
    protected $shipmentsService;
    /**
     * @var OrdersChannelSupportCache
     */
    protected $supportCache;
    /**
     * @var Proxy
     */
    protected $shipmentsProxy;

    /**
     * Handles update shipment request.
     *
     * @param UpdateShipmentRequest $request
     *
     * @throws FailedToRetrieveOrdersChannelSupportEntityException
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    public function handle(UpdateShipmentRequest $request)
    {
        if ($this->mustWaitForCompleteShipment($request)) {
            $items = $this->getShipmentsService()->getAllItems($request->getShopOrderId());
            foreach ($items as $item) {
                if (!$item->isShipped()) {
                    // We must wait until all items are shipped.
                    return;
                }
            }

            $request->setIsPartial(false);
        }

        try {
            $this->getShipmentsProxy()->updateShipment($request->getShopOrderId(), $this->prepareDto($request));
        } catch (BaseException $e) {
            $error = json_decode($e->getMessage(), true);
            $response = $this->getShipmentsService()->rejectUpdate($request, $e);

            if (!$response->isPermitted()) {
                $notification = new Notification();
                $notification->setNotificationContext(Context::ERROR);
                $notification->setCreatedAt(new DateTime());
                $notification->setMessage('Shipment upload finished with errors: %s');
                $notification->setArguments(isset($error['message']) ? $error['message'] : '');
                $notification->setContext(ConfigurationManager::getInstance()->getContext());

                $this->getNotificationService()->create($notification);
            }
        }
    }

    /**
     * Checks whether all items must be shipped before submitting request.
     *
     * @param UpdateShipmentRequest $request
     *
     * @return bool
     *
     * @throws FailedToRetrieveOrdersChannelSupportEntityException
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    protected function mustWaitForCompleteShipment(UpdateShipmentRequest $request)
    {
        return $request->isPartial()
            && !$this->getSupportCache()->isPartialShipmentSupported($request->getShopOrderId());
    }

    /**
     * @param UpdateShipmentRequest $request
     *
     * @return MerchantShipmentTrackingRequest
     */
    protected function prepareDto(UpdateShipmentRequest $request)
    {
        $dto = new MerchantShipmentTrackingRequest();

        $dto->setMethod($request->getMethod());
        $dto->setReturnTrackTraceNo($request->getReturnTrackTraceNo());
        $dto->setTrackTraceUrl($request->getTrackTraceUrl());
        $dto->setTrackTraceNo($request->getTrackTraceNo());

        return $dto;
    }

    /**
     * Retrieves an instance of ShipmentsService.
     *
     * @return ShipmentsService
     */
    protected function getShipmentsService()
    {
        if ($this->shipmentsService === null) {
            $this->shipmentsService = ServiceRegister::getService(ShipmentsService::class);
        }

        return $this->shipmentsService;
    }

    /**
     * Retrieves an instance of OrdersChannelSupportCache.
     *
     * @return OrdersChannelSupportCache
     */
    protected function getSupportCache()
    {
        if ($this->supportCache === null) {
            $this->supportCache = ServiceRegister::getService(OrdersChannelSupportCache::class);
        }

        return $this->supportCache;
    }

    /**
     * Retrieves an instance of ShipmentsProxy.
     *
     * @return Proxy
     */
    protected function getShipmentsProxy()
    {
        if ($this->shipmentsProxy === null) {
            $this->shipmentsProxy = ServiceRegister::getService(Proxy::class);
        }

        return $this->shipmentsProxy;
    }

    /**
     * @return TimeProvider
     */
    protected function getTimeProvider()
    {
        return ServiceRegister::getService(TimeProvider::CLASS_NAME);
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return ServiceRegister::getService(NotificationService::class);
    }
}
