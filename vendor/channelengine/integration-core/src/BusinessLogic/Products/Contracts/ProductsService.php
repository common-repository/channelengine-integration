<?php

namespace ChannelEngine\BusinessLogic\Products\Contracts;

use ChannelEngine\BusinessLogic\Products\Domain\Product;

/**
 * Interface ProductsService
 *
 * @package ChannelEngine\BusinessLogic\Products\Contracts
 */
interface ProductsService
{
    /**
     * Provides all product ids.
     *
     * @param int $page
     * @param int $limit
     *
     * @return string[] | int[]
     */
    public function getProductIds($page, $limit = 5000);

    /**
     * Provides list of products.
     *
     * @param array $ids
     *
     * @return Product[]
     */
    public function getProducts(array $ids);
}