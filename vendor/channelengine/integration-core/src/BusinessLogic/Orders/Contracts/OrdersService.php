<?php

namespace ChannelEngine\BusinessLogic\Orders\Contracts;

use ChannelEngine\BusinessLogic\API\Orders\DTO\Order;
use ChannelEngine\BusinessLogic\Orders\Domain\CreateResponse;

/**
 * Interface OrdersService
 *
 * @package ChannelEngine\BusinessLogic\Orders\Contracts
 */
interface OrdersService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Creates new orders in the shop system and
     * returns CreateResponse.
     *
     * @param Order $order
     *
     * @return CreateResponse
     */
    public function create(Order $order);

	/**
	 * Retrieves total number of orders for synchronization.
	 *
	 * @return int
	 */
    public function getOrdersCount();
}
