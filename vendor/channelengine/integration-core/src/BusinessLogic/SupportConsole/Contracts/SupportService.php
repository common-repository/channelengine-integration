<?php


namespace ChannelEngine\BusinessLogic\SupportConsole\Contracts;

/**
 * Interface SupportService
 *
 * @package ChannelEngine\BusinessLogic\SupportConsole\Contracts
 */
interface SupportService
{
    /**
     * Return system configuration parameters
     *
     * @return array
     */
    public function get();

    /**
     * Updates system configuration parameters
     *
     * @param array $payload
     *
     * @return mixed
     */
    public function update(array $payload);
}