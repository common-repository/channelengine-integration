<?php

namespace ChannelEngine\Infrastructure\AutoTest;

use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\Serializer\Interfaces\Serializable;
use ChannelEngine\Infrastructure\Serializer\Serializer;
use ChannelEngine\Infrastructure\TaskExecution\Task;

/**
 * Class AutoTestTask.
 *
 * @package ChannelEngine\Infrastructure\AutoTest
 */
class AutoTestTask extends Task
{
    /**
     * Dummy data for the task.
     *
     * @var string
     */
    protected $data;

    /**
     * AutoTestTask constructor.
     *
     * @param string $data Dummy data.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Transforms array into an serializable object,
     *
     * @param array $array Data that is used to instantiate serializable object.
     *
     * @return Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array)
    {
        return new static($array['data']);
    }

    /**
     * Transforms serializable object into an array.
     *
     * @return array Array representation of a serializable object.
     */
    public function toArray()
    {
        return array('data' => $this->data);
    }

    /**
     * String representation of object.
     *
     * @return string The string representation of the object or null.
     */
    public function serialize()
    {
        return Serializer::serialize(array($this->data));
    }

    /**
     * Constructs the object.
     *
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     */
    public function unserialize($serialized)
    {
        list($this->data) = Serializer::unserialize($serialized);
    }

    /**
     * Runs task logic.
     */
    public function execute()
    {
        $this->reportProgress(5);
        Logger::logInfo('Auto-test task started');

        $this->reportProgress(50);
        Logger::logInfo('Auto-test task parameters', 'Core', [$this->data]);

        $this->reportProgress(100);
        Logger::logInfo('Auto-test task ended');
    }
}
