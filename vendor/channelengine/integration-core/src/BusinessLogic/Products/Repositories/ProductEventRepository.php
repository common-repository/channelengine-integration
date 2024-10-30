<?php

namespace ChannelEngine\BusinessLogic\Products\Repositories;

use ChannelEngine\BusinessLogic\Products\Entities\ProductEvent;
use ChannelEngine\Infrastructure\ORM\Interfaces\RepositoryInterface;

/**
 * Interface ProductEventRepository
 *
 * @package ChannelEngine\BusinessLogic\Products\Repositories
 */
interface ProductEventRepository extends RepositoryInterface
{
    /**
     * Deletes multiple entities and returns success flag.
     *
     * @param ProductEvent[] $entities
     *
     * @return bool TRUE if operation succeeded; otherwise, FALSE.
     */
    public function batchDelete(array $entities);
}