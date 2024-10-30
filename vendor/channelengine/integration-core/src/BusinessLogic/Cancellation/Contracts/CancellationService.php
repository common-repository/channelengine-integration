<?php

namespace ChannelEngine\BusinessLogic\Cancellation\Contracts;

use ChannelEngine\BusinessLogic\Cancellation\Domain\CancellationItem;
use ChannelEngine\BusinessLogic\Cancellation\Domain\CancellationRequest;
use ChannelEngine\BusinessLogic\Cancellation\Domain\RejectResponse;
use Exception;

/**
 * Interface CancellationService
 *
 * @package ChannelEngine\BusinessLogic\Cancellation\Contracts
 */
interface CancellationService
{
    /**
     * Provides list of cancelled items for order.
     *
     * @param $orderId
     *
     * @return CancellationItem[]
     */
    public function getAllItems($orderId);

    /**
     * Rejects cancellation request.
     *
     * @param CancellationRequest $request
     * @param Exception $reason
     *
     * @return RejectResponse
     */
    public function reject(CancellationRequest $request, Exception $reason);
}