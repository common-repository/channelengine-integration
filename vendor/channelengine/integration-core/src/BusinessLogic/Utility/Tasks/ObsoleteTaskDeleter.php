<?php

namespace ChannelEngine\BusinessLogic\Utility\Tasks;

use ChannelEngine\Infrastructure\ORM\QueryFilter\Operators;
use ChannelEngine\Infrastructure\ORM\QueryFilter\QueryFilter;
use ChannelEngine\Infrastructure\ORM\RepositoryRegistry;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\TaskExecution\Task;
use ChannelEngine\Infrastructure\Utility\TimeProvider;

/**
 * Class ObsoleteTaskDeleter
 * @package ChannelEngine\BusinessLogic\Utility\Tasks
 */
class ObsoleteTaskDeleter extends Task
{
    const COMPLETED_KEEP_DAYS = 7;
    const FAILED_KEEP_DAYS = 30;

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->deleteFailed();

        $this->reportProgress(30);

        $this->deleteCompleted();

        $this->reportProgress(100);
    }

    private function deleteFailed()
    {
        $cutoff = $this->getTimeProvider()->getCurrentLocalTime();
        $cutoff = $cutoff->modify("- " . static::FAILED_KEEP_DAYS . " days");

        $filter = new QueryFilter();
        $filter->where('status', Operators::IN, [QueueItem::FAILED, QueueItem::ABORTED])
            ->where('lastUpdateTimestamp', Operators::LESS_THAN, $cutoff->getTimestamp());

        $this->getRepository()->deleteWhere($filter);
    }

    private function deleteCompleted()
    {
        $cutoff = $this->getTimeProvider()->getCurrentLocalTime();
        $cutoff = $cutoff->modify("- " . static::COMPLETED_KEEP_DAYS . " days");

        $filter = new QueryFilter();
        $filter->where('status', Operators::EQUALS, QueueItem::COMPLETED)
            ->where('lastUpdateTimestamp', Operators::LESS_THAN, $cutoff->getTimestamp());

        $this->getRepository()->deleteWhere($filter);
    }

    private function getRepository()
    {
        return RepositoryRegistry::getRepository(QueueItem::getClassName());
    }

    /**
     * @return TimeProvider
     */
    private function getTimeProvider()
    {
        return ServiceRegister::getService(TimeProvider::CLASS_NAME);
    }
}