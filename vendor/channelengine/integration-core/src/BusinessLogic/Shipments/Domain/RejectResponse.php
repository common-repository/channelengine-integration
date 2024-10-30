<?php


namespace ChannelEngine\BusinessLogic\Shipments\Domain;

/**
 * Class RejectResponse
 *
 * @package ChannelEngine\BusinessLogic\Shipments\Domain
 */
class RejectResponse
{
    /**
     * @var bool
     */
    protected $isPermitted;

    /**
     * RejectResponse constructor.
     *
     * @param bool $isPermitted
     */
    public function __construct($isPermitted)
    {
        $this->isPermitted = $isPermitted;
    }

    /**
     * @return bool
     */
    public function isPermitted()
    {
        return $this->isPermitted;
    }
}