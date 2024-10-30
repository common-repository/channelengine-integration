<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Tasks;

use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogAware as ITransactionalLogAware;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogService;
use ChannelEngine\BusinessLogic\TransactionLog\Traits\TransactionLogAware;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Task as BaseTask;

/**
 * Class TransactionalTask
 *
 * @package ChannelEngine\BusinessLogic\TransactionLog\Tasks
 */
abstract class TransactionalTask extends BaseTask implements ITransactionalLogAware
{
    use TransactionLogAware;

    public function reportProgress($progressPercent)
    {
        $this->getService()->updateSynchronizedEntities($this->getTransactionLog()->getId(), $this->getTransactionLog()->getSynchronizedEntities());

        parent::reportProgress($progressPercent);
    }

    /**
     * @return TransactionLogService
     */
    private function getService()
    {
        return ServiceRegister::getService(TransactionLogService::class);
    }
}