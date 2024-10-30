<?php

namespace ChannelEngine\BusinessLogic\API\Products\Http;

use ChannelEngine\BusinessLogic\API\Http\Authorized\AuthorizedProxy;
use ChannelEngine\BusinessLogic\API\Http\DTO\HttpRequest;
use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\BusinessLogic\API\Products\DTO\Product;
use ChannelEngine\Infrastructure\Data\Transformer;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class Proxy
 *
 * @package ChannelEngine\BusinessLogic\API\Products\Http
 */
class Proxy extends AuthorizedProxy
{
    /**
     * Deletes product.
     *
     * @param $merchantProductNumber
     *
     * @throws RequestNotSuccessfulException
     * @throws HttpCommunicationException
     * @throws QueryFilterInvalidParamException
     * @throws HttpRequestException
     */
    public function deleteProduct($merchantProductNumber)
    {
        $request = new HttpRequest("products/$merchantProductNumber");

        $this->delete($request);
    }

    /**
     * Deletes products in bulk.
     *
     * @param array $merchantProductNumbers
     *
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws RequestNotSuccessfulException
     */
    public function bulkDelete(array $merchantProductNumbers)
    {
        $request = new HttpRequest('products/bulkdelete');
        $request->setBody($merchantProductNumbers);

        $this->post($request);
    }

    /**
     * Uploads batch of products.
     *
     * @param Product[] $products
     *
     * @throws HttpCommunicationException
     * @throws QueryFilterInvalidParamException
     * @throws RequestNotSuccessfulException
     * @throws HttpRequestException
     */
    public function upload(array $products)
    {
        $request = new HttpRequest('products', Transformer::batchTransform($products));

        $this->post($request);
    }

	/**
	 * Uploads batch of products.
	 *
	 * @param Product[] $products
	 *
	 * @throws HttpCommunicationException
	 * @throws QueryFilterInvalidParamException
	 * @throws RequestNotSuccessfulException
	 * @throws HttpRequestException
	 */
	public function uploadWithoutStock(array $products)
	{
		$request = new HttpRequest('products', Transformer::batchTransform($products), ['ignoreStock' => 'true']);

		$this->post($request);
	}
}