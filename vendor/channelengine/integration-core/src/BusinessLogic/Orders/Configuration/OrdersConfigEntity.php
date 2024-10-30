<?php

namespace ChannelEngine\BusinessLogic\Orders\Configuration;

use ChannelEngine\Infrastructure\ORM\Configuration\EntityConfiguration;
use ChannelEngine\Infrastructure\ORM\Configuration\IndexMap;
use ChannelEngine\Infrastructure\ORM\Entity;

/**
 * Class OrdersConfigEntity
 *
 * @package ChannelEngine\BusinessLogic\OrdersConfiguration
 */
class OrdersConfigEntity extends Entity
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $key;
    /**
     * @var string
     */
    protected $value;
    /**
     * Configuration context identifier.
     *
     * @var string
     */
    protected $context;
    /**
     * @var array
     */
    protected $fields = ['id', 'key', 'value', 'context'];

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $map = new IndexMap();
        $map->addStringIndex('key')
            ->addStringIndex('context');

        return new EntityConfiguration($map, 'OrdersConfigEntity');
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param  string  $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param  string  $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Sets context on config entity.
     *
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Retrieves config value context.
     *
     * @return string Context value.
     */
    public function getContext()
    {
        return $this->context;
    }
}
