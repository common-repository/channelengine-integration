<?php

namespace ChannelEngine\BusinessLogic\Products\Contracts;

use ChannelEngine\BusinessLogic\Products\Entities\SyncConfig;

interface ProductsSyncConfigService
{
    /**
     * Provides saved config.
     *
     * @return SyncConfig | null
     */
    public function get();

    /**
     * Saves sync config.
     *
     * @param SyncConfig $config
     *
     * @return void
     */
    public function set(SyncConfig $config);
}