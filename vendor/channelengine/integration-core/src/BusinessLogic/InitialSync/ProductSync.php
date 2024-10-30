<?php

namespace ChannelEngine\BusinessLogic\InitialSync;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\Products\Tasks\ProductsUpsertTask;
use ChannelEngine\BusinessLogic\TransactionLog\Tasks\TransactionalOrchestrator;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\Serializer\Serializer;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Composite\ExecutionDetails;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

class ProductSync extends TransactionalOrchestrator
{
    const PRODUCTS_PER_BATCH = 5000;

    protected $page = 0;

    public function toArray()
    {
        $result = parent::toArray();
        $result['page'] = $this->page;

        return $result;
    }

    public static function fromArray(array $array)
    {
        $entity = parent::fromArray($array);
        $entity->page = $array['page'];

        return $entity;
    }

    public function serialize()
    {
        return Serializer::serialize([
            'parent' => parent::serialize(),
            'page' => $this->page
        ]);
    }

    public function unserialize($serialized)
    {
        $unserialized = Serializer::unserialize($serialized);
        parent::unserialize($unserialized['parent']);
        $this->page = $unserialized['page'];
    }

    /**
     * Creates subtask.
     *
     * @return ExecutionDetails | null
     *
     * @throws QueueStorageUnavailableException
     */
    protected function getSubTask()
    {
        $ids = $this->getService()->getProductIds($this->page, static::PRODUCTS_PER_BATCH);
        if (empty($ids)) {
            return null;
        }

        try {
            if (ConfigurationManager::getInstance()->getConfigValue('syncProducts', 1)) {
                $this->page++;

                return $this->createSubJob(new ProductsUpsertTask($ids));
            }
        } catch (QueryFilterInvalidParamException $exception) {
            Logger::logError($exception->getMessage());

            return null;
        }
    }


    /**
     * Provides product service.
     *
     * @return ProductsService
     */
    protected function getService()
    {
        return ServiceRegister::getService(ProductsService::class);
    }
}