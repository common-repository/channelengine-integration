<?php

namespace ChannelEngine\Infrastructure\Logger;

use ChannelEngine\Infrastructure\Data\DataTransferObject;

/**
 * Class LogContextData.
 *
 * @package ChannelEngine\Infrastructure\Logger
 */
class LogContextData extends DataTransferObject
{
    /**
     * Name of data.
     *
     * @var string
     */
    private $name;
    /**
     * Value of data.
     *
     * @var mixed
     */
    private $value;

    /**
     * LogContextData constructor.
     *
     * @param string $name Name of data.
     * @param mixed $value Value of data.
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Gets name of data.
     *
     * @return string Name of data.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets value of data.
     *
     * @return mixed Value of data.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data)
    {
        return new self($data['name'], $data['value']);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'name' => $this->getName(),
            'value' => $this->getValue(),
        );
    }
}
