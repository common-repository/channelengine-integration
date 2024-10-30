<?php

namespace ChannelEngine\BusinessLogic\API\Shipments\Http;

use ChannelEngine\BusinessLogic\API\Http\Authorized\AuthorizedProxy;
use ChannelEngine\BusinessLogic\API\Http\DTO\HttpRequest;
use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\BusinessLogic\API\Shipments\DTO\MerchantShipmentRequest;
use ChannelEngine\BusinessLogic\API\Shipments\DTO\MerchantShipmentTrackingRequest;
use ChannelEngine\BusinessLogic\API\Shipments\DTO\Shipment;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class Proxy
 *
 * @package ChannelEngine\BusinessLogic\API\Shipments\Http
 */
class Proxy extends AuthorizedProxy
{
    /**
     * Creates shipment.
     *
     * @param MerchantShipmentRequest $shipmentRequest
     *
     * @return void
     *
     * @throws RequestNotSuccessfulException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function createShipment(MerchantShipmentRequest $shipmentRequest)
    {
        $request = new HttpRequest('shipments');
        $request->setBody($shipmentRequest->toArray());
        $this->post($request);
    }

    /**
     * Update an existing shipment with tracking information.
     *
     * @param $merchantShipmentNo
     * @param MerchantShipmentTrackingRequest $trackingRequest
     *
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws RequestNotSuccessfulException
     */
    public function updateShipment($merchantShipmentNo, MerchantShipmentTrackingRequest $trackingRequest)
    {
        $request = new HttpRequest('shipments/' . $merchantShipmentNo);
        $request->setBody($trackingRequest->toArray());
        $this->put($request);
    }

    /**
     * Retrieves shipment from ChannelEngine by merchant order number.
     *
     * @param string $merchantOrderNo
     *
     * @return Shipment[]
     *
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws RequestNotSuccessfulException
     */
    public function getShipmentByMerchantOrderNo($merchantOrderNo)
    {
        $request = new HttpRequest('shipments/merchant', [], ['merchantOrderNos' => $merchantOrderNo]);
        $response = $this->get($request);
        $responseBody = $response->decodeBodyToArray();

        return isset($responseBody['Content']) ? Shipment::fromBatch($responseBody['Content']) : null;
    }
}
