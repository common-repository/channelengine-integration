<?php

namespace ChannelEngine\Infrastructure\TaskExecution\Exceptions;

use ChannelEngine\Infrastructure\Exceptions\BaseException;
use Exception;

/**
 * Class QueueStorageUnavailableException.
 *
 * @package ChannelEngine\Infrastructure\TaskExecution\Exceptions
 */
class QueueStorageUnavailableException extends BaseException
{
    /**
     * QueueStorageUnavailableException constructor.
     *
     * @param string $message Exception message.
     * @param Exception $previous Exception instance that was thrown.
     */
    public function __construct($message = '', $previous = null)
    {
        parent::__construct(trim($message . ' Queue storage failed to save item.'), 0, $previous);
    }
}
