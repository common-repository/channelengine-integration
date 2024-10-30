<?php

namespace ChannelEngine\BusinessLogic\Products\Tasks;

use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\BusinessLogic\API\Products\Http\Proxy;
use ChannelEngine\BusinessLogic\Notifications\Contracts\Context;
use ChannelEngine\BusinessLogic\Notifications\Traits\NotificationCreator;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\BusinessLogic\Products\Domain\Product;
use ChannelEngine\BusinessLogic\Products\Domain\Variant;
use ChannelEngine\BusinessLogic\Products\Transformers\ApiProductTransformer;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\DetailsService;
use ChannelEngine\BusinessLogic\TransactionLog\Tasks\TransactionalTask;
use ChannelEngine\Infrastructure\Exceptions\BaseException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\Serializer\Serializer;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class ProductsUpsertTask
 *
 * @package ChannelEngine\BusinessLogic\Products\Tasks
 */
class ProductsUpsertTask extends TransactionalTask
{
    use NotificationCreator;

    /**
     * @var string[]
     */
    protected $productIds;
    /**
     * @var int
     */
    protected $reconfiguredBatchSize = 0;
    /**
     * @var array
     */
    protected $exportedProductIds = [];
    /**
     * @var array
     */
    protected $exportedVariantIds = [];
    /**
     * @var int
     */
    protected $syncedNumber = 0;
    /**
     * @var int
     */
    protected $totalNumberOfProducts = 0;
    /**
     * @var int
     */
    protected $failedNumber = 0;

    /**
     * ProductsUpsertTask constructor.
     *
     * @param string[] $productIds
     */
    public function __construct(array $productIds)
    {
        $this->productIds = $productIds;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'productIds' => $this->productIds,
            'reconfiguredBatchSize' => $this->reconfiguredBatchSize,
            'exportedProductIds' => $this->exportedProductIds,
            'exportedVariantIds' => $this->exportedVariantIds,
            'syncedNumber' => $this->syncedNumber,
            'totalNumberOfProducts' => $this->totalNumberOfProducts,
            'failedNumber' => $this->failedNumber,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        $task = new static($array['productIds']);
        $task->reconfiguredBatchSize = $array['reconfiguredBatchSize'];
        $task->exportedProductIds = $array['exportedProductIds'];
        $task->exportedVariantIds = $array['exportedVariantIds'];
        $task->syncedNumber = $array['syncedNumber'];
        $task->totalNumberOfProducts = $array['totalNumberOfProducts'];
        $task->failedNumber = $array['failedNumber'];

        return $task;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize(
            [
                'productIds' => $this->productIds,
                'reconfiguredBatchSize' => $this->reconfiguredBatchSize,
                'exportedProductIds' => $this->exportedProductIds,
                'exportedVariantIds' => $this->exportedVariantIds,
                'syncedNumber' => $this->syncedNumber,
                'totalNumberOfProducts' => $this->totalNumberOfProducts,
                'failedNumber' => $this->failedNumber,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $unserialized = Serializer::unserialize($serialized);

        $this->productIds = $unserialized['productIds'];
        $this->reconfiguredBatchSize = $unserialized['reconfiguredBatchSize'];
        $this->exportedProductIds = $unserialized['exportedProductIds'];
        $this->exportedVariantIds = $unserialized['exportedVariantIds'];
        $this->syncedNumber = $unserialized['syncedNumber'];
        $this->totalNumberOfProducts = $unserialized['totalNumberOfProducts'];
        $this->failedNumber = $unserialized['failedNumber'];
    }

    /**
     * Checks whether task can be reconfigured or not.
     *
     * @return bool
     */
    public function canBeReconfigured()
    {
        return $this->reconfiguredBatchSize !== 1;
    }

    /**
     * Reconfigures task.
     */
    public function reconfigure()
    {
        if ($this->reconfiguredBatchSize === 0) {
            $this->reconfiguredBatchSize = $this->getConfigService()->getSynchronizationBatchSize();
        }

        $this->reconfiguredBatchSize = ceil($this->reconfiguredBatchSize / 2);
    }

    /**
     * Exports products to ChannelEngine API.
     */
    public function execute()
    {
        $this->initializeNumberOfProducts();
        $batch = $this->getBatchOfProductIds();

        while (!empty($batch)) {
            $batchOfProducts = [];
            $products = $this->getProductsService()->getProducts($batch);

            foreach ($products as $product) {
                if ($this->isProductExported($product)) {
                    continue;
                }

                if ($this->isExportBatchFull($batchOfProducts)) {
                    $this->exportBatch($batchOfProducts);
                }

                $batchOfProducts[] = ApiProductTransformer::transformDomainProduct($product);
                $this->markExportedProduct($product);

                $this->addVariantsToBatch($product, $batchOfProducts);
            }

            if (!empty($batchOfProducts)) {
                $this->exportBatch($batchOfProducts);
            }

            $this->unsetExportedIds($batch);

            $batch = $this->getBatchOfProductIds();

            $this->updateTransactionLog();
            $this->reportProgress($this->getCurrentProgress());
        }

        $this->updateTransactionLog();

        if ($this->failedNumber > 0) {
            $this->addTaskSummary(
                $this->getTransactionLog(),
                'Products upload finished with errors.',
                $this->getTransactionLog()->getSynchronizedEntities() === 0 ? Context::ERROR : Context::WARNING
            );
        }

        $this->reportProgress(100);
    }

    protected function updateTransactionLog()
    {
        $numberOfSynced = $this->getTransactionLog()->getSynchronizedEntities();
        $this->getTransactionLog()->setSynchronizedEntities(
            $numberOfSynced ?
                $numberOfSynced + $this->syncedNumber : $this->syncedNumber
        );
        $this->syncedNumber = 0;
    }

    /**
     * Adds product variants to export batch.
     *
     * @param Product $product
     * @param array $batchOfProducts
     */
    protected function addVariantsToBatch(Product $product, &$batchOfProducts)
    {
        foreach ($product->getVariants() as $variant) {
            if ($this->isVariantExported($variant, $product)) {
                continue;
            }

            $this->totalNumberOfProducts++;

            if ($this->isExportBatchFull($batchOfProducts)) {
                $this->exportBatch($batchOfProducts);
                $this->totalNumberOfProducts++;
                $batchOfProducts[] = ApiProductTransformer::transformDomainProduct($product);
            }

            $batchOfProducts[] = ApiProductTransformer::transformVariant($variant);
            $this->markExportedVariant($variant, $product);
        }
    }

    /**
     * Exports batch of products to ChannelEngine API.
     *
     * @param $batchOfProducts
     */
    protected function exportBatch(&$batchOfProducts)
    {
        try {
            $this->exportProducts($batchOfProducts);
        } catch (BaseException $e) {
            $this->handleExportError($e, $batchOfProducts);
        }

        $batchOfProducts = [];
    }

    /**
     * @param $batchOfProducts
     *
     * @throws RequestNotSuccessfulException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    protected function exportProducts(&$batchOfProducts)
    {
		$syncConfig = $this->getProductsSyncConfigService()->get();
	    if( $syncConfig === null || $syncConfig->isEnabledStockSync() ) {
		    $this->getProductsProxy()->upload($batchOfProducts);
	    } else {
		    $this->getProductsProxy()->uploadWithoutStock($batchOfProducts);
	    }

        $this->syncedNumber += count($batchOfProducts);
    }

    /**
     * @param BaseException $exception
     * @param array $batchOfProducts
     */
    protected function handleExportError(BaseException $exception, &$batchOfProducts)
    {
        $this->getTransactionLog()->setHasErrors(true);
        $productIndexes = $this->getProductIndexes($exception, $batchOfProducts);
        $this->failedNumber += count($productIndexes);
        $this->createExceptionDetails($exception, $batchOfProducts, $productIndexes);

        $batchOfProducts = array_values($this->removeFailedProducts($batchOfProducts, $productIndexes));
        try {
            $this->exportProducts($batchOfProducts);
        } catch (BaseException $e) {
            $productIndexes = $this->getProductIndexes($e, $batchOfProducts);
            $this->failedNumber += count($productIndexes);
            $this->createExceptionDetails($e, $batchOfProducts, $productIndexes);
        }
    }

    /**
     * @param BaseException $exception
     * @param array $batchOfProducts
     * @param array $productIndexes
     */
    protected function createExceptionDetails(BaseException $exception, &$batchOfProducts, $productIndexes)
    {
        $error = json_decode($exception->getMessage(), true);
        $errorMessages = isset($error['errorMessages']) ? $error['errorMessages'] : [];
        $validationErrors = isset($error['validationErrors']) ? $error['validationErrors'] : [];

        foreach ($productIndexes as $key => $index) {
            if (!isset($batchOfProducts[$key])) {
                continue;
            }

            $this->addLogDetail(
                $batchOfProducts[$key]->getMerchantProductNo(),
                $this->formatErrorMessage($errorMessages, $validationErrors, $index)
            );
        }
    }

    /**
     * @param array $batchOfProducts
     * @param array $indexes
     *
     * @return array
     */
    protected function removeFailedProducts(&$batchOfProducts, $indexes)
    {
        foreach ($indexes as $key => $index) {
            unset($batchOfProducts[$key]);
        }

        return $batchOfProducts;
    }

    /**
     * @param BaseException $exception
     * @param array $batchOfProducts
     *
     * @return array
     */
    protected function getProductIndexes($exception, &$batchOfProducts)
    {
        $error = json_decode($exception->getMessage(), true);
        $errorMessages = isset($error['errorMessages']) ?
            $error['errorMessages'] : [];
        $validationErrors = isset($error['validationErrors']) ? $error['validationErrors'] : [];
        $errorKeys = array_keys($validationErrors);

        foreach ($errorMessages as $errorMessage) {
            $errorKeys[] = isset($errorMessage['Reference']) ? $errorMessage['Reference'] : '';
        }

        Logger::logError('Product synchronization failed because: ' . $exception->getMessage());

        $productIndexes = [];

        foreach ($batchOfProducts as $key => $product) {
            $productIndexes[$key][] = in_array((string)$product->getMerchantProductNo(), $errorKeys, true) ?
                $product->getMerchantProductNo() : null;

            if (preg_grep('/\[' . $key . '].*/', $errorKeys)) {
                foreach (preg_grep('/\[' . $key . '].*/', $errorKeys) as $item) {
                    $productIndexes[$key][] = $item;
                }
            }

            $productIndexes[$key] = array_filter($productIndexes[$key]);
        }

        return array_filter($productIndexes);
    }

    /**
     * @param array $errorMessages
     * @param array $validationErrors
     * @param array $errorKeys
     *
     * @return string
     */
    protected function formatErrorMessage($errorMessages, $validationErrors, $errorKeys)
    {
        $message = '';

        foreach ($errorKeys as $key) {
            foreach ($errorMessages as $errorMessage) {
                if (isset($errorMessage['Reference']) && $errorMessage['Reference'] === (string)$key) {
                    foreach ($errorMessage['Errors'] as $error) {
                        $message .= ' ' . $error;
                    }
                }
            }
        }

        foreach ($errorKeys as $errorKey) {
            $message .= ' ' . (isset($validationErrors[$errorKey]) ? $validationErrors[$errorKey][0] : '');
        }

        return $message ?: 'Unknown error.';
    }

    /**
     * Adds log details.
     */
    protected function addLogDetail($id, $error)
    {
        $this->getDetailsService()->create(
            $this->getTransactionLog(),
            'Failed to synchronize product %s because: %s',
            [
                $id,
                $error,
            ]
        );
    }

    /**
     * @return DetailsService
     */
    protected function getDetailsService()
    {
        return ServiceRegister::getService(DetailsService::class);
    }

    /**
     * Checks if export batch is full.
     *
     * @param $batchOfProducts
     *
     * @return bool
     */
    protected function isExportBatchFull($batchOfProducts)
    {
        return count($batchOfProducts) >= $this->getBatchSize();
    }

    /**
     * Adds product id to exportedProductIds.
     *
     * @param Product $product
     */
    protected function markExportedProduct(Product $product)
    {
        $this->exportedProductIds[] = $product->getId();
    }

    /**
     * Adds variant id to exportedVariantIds.
     *
     * @param Variant $variant
     * @param Product $product
     */
    protected function markExportedVariant(Variant $variant, Product $product)
    {
        $this->exportedVariantIds[$product->getId()][] = $variant->getId();
    }

    /**
     * Checks if product is already exported.
     *
     * @param Product $product
     *
     * @return bool
     */
    protected function isProductExported(Product $product)
    {
        return in_array($product->getId(), $this->exportedProductIds, true);
    }

    /**
     * Checks if variant is already exported.
     *
     * @param Variant $variant
     * @param Product $product
     *
     * @return bool
     */
    protected function isVariantExported(Variant $variant, Product $product)
    {
        return isset($this->exportedVariantIds[$product->getId()]) &&
            in_array($variant->getId(), $this->exportedVariantIds[$product->getId()], true);
    }

    /**
     * Splits product ids into batches.
     *
     * @return array
     */
    protected function getBatchOfProductIds()
    {
        return array_slice($this->productIds, 0, $this->getBatchSize(), true);
    }

    /**
     * Returns current batch size.
     *
     * @return int
     */
    protected function getBatchSize()
    {
        if ($this->reconfiguredBatchSize !== 0) {
            return $this->reconfiguredBatchSize;
        }

        return $this->getConfigService()->getSynchronizationBatchSize();
    }

    /**
     * Unsets exported product ids.
     *
     * @param array $ids
     */
    protected function unsetExportedIds($ids)
    {
        foreach ($this->productIds as $key => $productId) {
            if (in_array($productId, $ids, true)) {
                unset($this->productIds[$key]);
            }
        }
    }

    /**
     * Retrieves current progress.
     *
     * @return float
     */
    protected function getCurrentProgress()
    {
        return ($this->syncedNumber * 95.0) / $this->totalNumberOfProducts;
    }

    /**
     * Initializes total number of products.
     *
     * @return int
     */
    protected function initializeNumberOfProducts()
    {
        if ($this->totalNumberOfProducts === 0) {
            $this->totalNumberOfProducts = count($this->productIds);
        }

        return $this->totalNumberOfProducts ?: 1;
    }

    /**
     * Retrieves instance of ProductsService.
     *
     * @return ProductsService
     */
    protected function getProductsService()
    {
        return ServiceRegister::getService(ProductsService::class);
    }

	/**
	 * Retrieves instance of ProductsSyncConfigService.
	 *
	 * @return ProductsSyncConfigService
	 */
	protected function getProductsSyncConfigService()
	{
		return ServiceRegister::getService(ProductsSyncConfigService::class);
	}

    /**
     * Retrieves instance of product Proxy.
     *
     * @return Proxy
     */
    protected function getProductsProxy()
    {
        return ServiceRegister::getService(Proxy::class);
    }
}
