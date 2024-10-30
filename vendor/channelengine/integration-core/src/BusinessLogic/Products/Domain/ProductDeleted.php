<?php

namespace ChannelEngine\BusinessLogic\Products\Domain;

/**
 * Class ProductDeleted
 *
 * @package ChannelEngine\BusinessLogic\Products\Domain
 */
class ProductDeleted
{
    /**
     * @var string
     */
    protected $id;

    /**
     * ProductDeleted constructor.
     *
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return (string)$this->id;
    }
}