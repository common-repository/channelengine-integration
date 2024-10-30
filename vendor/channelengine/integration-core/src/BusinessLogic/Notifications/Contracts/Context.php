<?php

namespace ChannelEngine\BusinessLogic\Notifications\Contracts;

/**
 * Interface Context
 *
 * @package ChannelEngine\BusinessLogic\Notifications\Contracts
 */
interface Context
{
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const SUCCESS = 'success';
    const NONE = 'none';
}