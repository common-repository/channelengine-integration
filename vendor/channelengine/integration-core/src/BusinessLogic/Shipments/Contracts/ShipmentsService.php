<?php


namespace ChannelEngine\BusinessLogic\Shipments\Contracts;

use ChannelEngine\BusinessLogic\Shipments\Domain\CreateShipmentRequest;
use ChannelEngine\BusinessLogic\Shipments\Domain\OrderItem;
use ChannelEngine\BusinessLogic\Shipments\Domain\RejectResponse;
use ChannelEngine\BusinessLogic\Shipments\Domain\UpdateShipmentRequest;
use Exception;

/**
 * Interface ShipmentsService
 *
 * @package ChannelEngine\BusinessLogic\Shipments\Contracts
 */
interface ShipmentsService
{
    /**
     * Retrieves all order items by order id.
     *
     * @param string $shopOrderId
     *
     * @return OrderItem[]
     */
    public function getAllItems($shopOrderId);

    /**
     * Rejects creation request.
     *
     * @param CreateShipmentRequest $request
     * @param Exception $reason
     *
     * @return RejectResponse
     */
    public function rejectCreate($request, Exception $reason);

    /**
     * Rejects update request.
     *
     * @param UpdateShipmentRequest $request
     * @param Exception $reason
     *
     * @return RejectResponse
     */
    public function rejectUpdate($request, Exception $reason);
}