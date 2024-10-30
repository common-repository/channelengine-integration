<?php

namespace ChannelEngine\BusinessLogic\Orders\ChannelSupport;

use ChannelEngine\BusinessLogic\API\Orders\DTO\Order;
use ChannelEngine\BusinessLogic\API\Orders\Http\Proxy;
use ChannelEngine\BusinessLogic\Orders\ChannelSupport\Exceptions\FailedToRetrieveOrdersChannelSupportEntityException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\ORM\Interfaces\RepositoryInterface;
use ChannelEngine\Infrastructure\ORM\QueryFilter\Operators;
use ChannelEngine\Infrastructure\ORM\QueryFilter\QueryFilter;
use ChannelEngine\Infrastructure\ORM\RepositoryRegistry;
use ChannelEngine\Infrastructure\ServiceRegister;
use Exception;

/**
 * Class OrdersChannelSupportCache
 *
 * @package ChannelEngine\BusinessLogic\Orders\ChannelSupport
 */
class OrdersChannelSupportCache
{
    const CLASS_NAME = __CLASS__;

    /**
     * Caches order channel support.
     *
     * @param string $shopOrderId
     * @param string $channelSupport
     *
     * @throws RepositoryNotRegisteredException
     */
    public function create($shopOrderId, $channelSupport)
    {
        $supportEntity = new OrdersChannelSupportEntity();
        $supportEntity->setShopOrderId($shopOrderId);
        $supportEntity->setChannelEngineSupport($channelSupport);

        $this->getOrdersChannelSupportEntityRepository()->save($supportEntity);
    }

    /**
     * Checks if partial cancellation is supported for given shopOrderId.
     *
     * @param string $shopOrderId
     *
     * @return bool
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     * @throws FailedToRetrieveOrdersChannelSupportEntityException
     */
    public function isPartialCancellationSupported($shopOrderId)
    {
        $supportEntity = $this->getSupportEntity($shopOrderId);

        if (!$supportEntity) {
            $order = $this->getOrderByShopOrderId($shopOrderId);

            return $order->getChannelOrderSupport() === "SPLIT_ORDERS";
        }

        return $supportEntity->getChannelEngineSupport() === "SPLIT_ORDERS";
    }

    /**
     * Checks if partial shipment is supported for given shopOrderId.
     *
     * @param string $shopOrderId
     *
     * @return bool
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     * @throws FailedToRetrieveOrdersChannelSupportEntityException
     */
    public function isPartialShipmentSupported($shopOrderId)
    {
        $supportEntity = $this->getSupportEntity($shopOrderId);

        if (!$supportEntity) {
            $order = $this->getOrderByShopOrderId($shopOrderId);

            return $order->getChannelOrderSupport() === "SPLIT_ORDER_LINES";
        }

        return $supportEntity->getChannelEngineSupport() === "SPLIT_ORDER_LINES";
    }

    /**
     * @param $shopOrderId
     *
     * @return OrdersChannelSupportEntity | null
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    protected function getSupportEntity($shopOrderId)
    {
        $filter = new QueryFilter();
        $filter->where('shopOrderId', Operators::EQUALS, $shopOrderId);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getOrdersChannelSupportEntityRepository()->selectOne($filter);
    }

    /**
     * Retrieves order from Channel Engine API by shop order id.
     *
     * @param $shopOrderId
     *
     * @return Order
     *
     * @throws FailedToRetrieveOrdersChannelSupportEntityException
     */
    protected function getOrderByShopOrderId($shopOrderId)
    {
        try {
            $orders = $this->getOrderProxy()->getOrdersByMerchantOrderNumbers([$shopOrderId]);
            $this->create($shopOrderId, $orders[0]->getChannelOrderSupport());
        } catch (Exception $e) {
            throw new FailedToRetrieveOrdersChannelSupportEntityException(
                'Failed to retrieve OrdersChannelSupportEntity for shop order id ' . $shopOrderId
            );
        }

        return $orders[0];
    }

    /**
     * Retrieves OrdersChannelSupportEntity repository.
     *
     * @return RepositoryInterface
     *
     * @throws RepositoryNotRegisteredException
     */
    protected function getOrdersChannelSupportEntityRepository()
    {
        return RepositoryRegistry::getRepository(OrdersChannelSupportEntity::CLASS_NAME);
    }

    /**
     * Retrieves instance of order proxy.
     *
     * @return Proxy
     */
    protected function getOrderProxy()
    {
        return ServiceRegister::getService(Proxy::CLASS_NAME);
    }
}
