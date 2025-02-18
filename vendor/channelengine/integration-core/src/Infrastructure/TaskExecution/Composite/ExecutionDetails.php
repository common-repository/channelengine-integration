<?php

namespace ChannelEngine\Infrastructure\TaskExecution\Composite;

use ChannelEngine\Infrastructure\Serializer\Interfaces\Serializable;
use ChannelEngine\Infrastructure\Serializer\Serializer;

/**
 * Class ExecutionDetails
 *
 * @package ChannelEngine\Infrastructure\TaskExecution\Composite
 *
 * @access private
 */
class ExecutionDetails implements Serializable
{
    /**
     * Execution id.
     *
     * @var int
     */
    private $executionId;
    /**
     * Positive (grater than zero) integer. Higher number implies higher impact of subtask's progress on total progress.
     *
     * @var int
     */
    private $weight;
    /**
     * Task progress.
     *
     * @var float
     */
    private $progress;

    /**
     * ExecutionDetails constructor.
     * @param int $executionId
     * @param int $weight
     */
    public function __construct($executionId, $weight = 1)
    {
        $this->executionId = $executionId;
        $this->weight = $weight;
        $this->progress = 0.0;
    }

    /**
     * @return int
     */
    public function getExecutionId()
    {
        return $this->executionId;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return float
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param float $progress
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize([$this->executionId, $this->weight, $this->progress]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($data)
    {
        list($this->executionId, $this->weight, $this->progress) = Serializer::unserialize($data);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'progress' => $this->getProgress(),
            'executionId' => $this->getExecutionId(),
            'weight' => $this->getWeight(),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        $entity = new static($array['executionId'], $array['weight']);
        $entity->setProgress($array['progress']);

        return $entity;
    }
}