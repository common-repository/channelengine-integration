<?php


/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpDocMissingThrowsInspection */

namespace ChannelEngine\BusinessLogic\Products;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductEventsBufferService as BaseService;
use ChannelEngine\BusinessLogic\Products\Domain\ProductDeleted;
use ChannelEngine\BusinessLogic\Products\Domain\ProductUpsert;
use ChannelEngine\BusinessLogic\Products\Entities\ProductEvent;
use ChannelEngine\BusinessLogic\Products\Repositories\ProductEventRepository;
use ChannelEngine\Infrastructure\Configuration\Configuration;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ORM\QueryFilter\Operators;
use ChannelEngine\Infrastructure\ORM\QueryFilter\QueryFilter;
use ChannelEngine\Infrastructure\ORM\RepositoryRegistry;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class ProductEventsBufferService
 *
 * @package ChannelEngine\BusinessLogic\Products
 */
class ProductEventsBufferService implements BaseService
{
    /**
     * @inheritDoc
     */
    public function recordDeleted(ProductDeleted $deleted)
    {
        $productEvent = $this->getProductEvent($deleted->getId());

        if (!$productEvent) {
            $this->eventCreate($deleted->getId(), ProductEvent::DELETED);

            return;
        }

        $this->eventUpdate($productEvent, ProductEvent::DELETED);
    }

    /**
     * @inheritDoc
     */
    public function recordUpsert(ProductUpsert $upsert)
    {
        $productId = $upsert->isVariant() ? $upsert->getParentId() : $upsert->getId();
        $productEvent = $this->getProductEvent($productId);

        if (!$productEvent) {
            $this->eventCreate($productId, ProductEvent::UPSERT);

            return;
        }

        $this->eventUpdate($productEvent, ProductEvent::UPSERT);
    }

    /**
     * @inheritDoc
     */
    public function get($type, $offset, $limit)
    {
        $filter = new QueryFilter();
        $filter->where('type', Operators::EQUALS, $type);
        $filter->setOffset($offset);
        $filter->setLimit($limit);

        return $this->getRepository()->select($filter);
    }

    /**
     * @inheritDoc
     */
    public function delete(array $events)
    {
        $this->getRepository()->batchDelete($events);
    }

    /**
     * @inheritDoc
     */
    public function getLastReadTime()
    {
        return $this->getConfigService()->getLastEventsReadTime();
    }

    /**
     * @inheritDoc
     */
    public function updateLastReadTime($lastReadTime)
    {
        $this->getConfigService()->setLastEventsReadTime($lastReadTime);
    }

    /**
     * Gets product event by id.
     *
     * @param $id
     *
     * @noinspection PhpReturnDocTypeMismatchInspection
     * @return ProductEvent | null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getProductEvent($id)
    {
        $filter = new QueryFilter();
        $filter->where('productId', Operators::EQUALS, $id);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getRepository()->selectOne($filter);
    }

    /**
     * Creates new product event.
     *
     * @param string $id
     * @param string $type
     */
    protected function eventCreate($id, $type)
    {
        $event = new ProductEvent();
        $event->setType($type);
        $event->setProductId($id);
        $this->getRepository()->save($event);
    }

    /**
     * Updates existing product event.
     *
     * @param ProductEvent $productEvent
     * @param string $type
     */
    protected function eventUpdate(ProductEvent $productEvent, $type)
    {
        $productEvent->setType($type);
        $this->getRepository()->update($productEvent);
    }

    /**
     * Provides repository.
     *
     * @return ProductEventRepository
     */
    protected function getRepository()
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return RepositoryRegistry::getRepository(ProductEvent::getClassName());
    }

    /**
     * Retrieves instance of Configuration.
     *
     * @return Configuration
     */
    protected function getConfigService()
    {
        return ServiceRegister::getService(Configuration::class);
    }
}