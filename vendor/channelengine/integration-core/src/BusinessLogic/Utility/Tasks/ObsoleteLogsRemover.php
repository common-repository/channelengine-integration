<?php

namespace ChannelEngine\BusinessLogic\Utility\Tasks;

use ChannelEngine\BusinessLogic\Notifications\Entities\Notification;
use ChannelEngine\BusinessLogic\TransactionLog\Entities\Details;
use ChannelEngine\BusinessLogic\TransactionLog\Entities\TransactionLog;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\ORM\Interfaces\RepositoryInterface;
use ChannelEngine\Infrastructure\ORM\QueryFilter\Operators;
use ChannelEngine\Infrastructure\ORM\QueryFilter\QueryFilter;
use ChannelEngine\Infrastructure\ORM\RepositoryRegistry;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Task;
use ChannelEngine\Infrastructure\Utility\TimeProvider;
use DateTime;

class ObsoleteLogsRemover extends Task
{
    const LOG_KEEP_DAYS = 30;

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    public function execute()
    {
        $cutoff = $this->getTimeProvider()->getCurrentLocalTime();
        $cutoff = $cutoff->modify("- " . static::LOG_KEEP_DAYS . " days");

        $this->reportProgress(5);

        $this->deleteLogs($cutoff);

        $this->reportProgress(70);

        $this->deleteDetails($cutoff);

        $this->reportProgress(90);

        $this->deleteNotifications($cutoff);

        $this->reportProgress(100);
    }

    private function getLogRepository()
    {
        return RepositoryRegistry::getRepository(TransactionLog::getClassName());
    }

    private function getDetailsRepository()
    {
        return RepositoryRegistry::getRepository(Details::getClassName());
    }

    /**
     * @return RepositoryInterface
     *
     * @throws RepositoryNotRegisteredException
     */
    private function getNotificationsRepository()
    {
        return RepositoryRegistry::getRepository(Notification::getClassName());
    }

    /**
     * @return TimeProvider
     */
    private function getTimeProvider()
    {
        return ServiceRegister::getService(TimeProvider::CLASS_NAME);
    }

    private function deleteLogs(DateTime $cutoff)
    {
        $filter = new QueryFilter();
        $filter->where('startTime', Operators::LESS_THAN, $cutoff);

        $this->getLogRepository()->deleteWhere($filter);
    }

    private function deleteDetails(DateTime $cutoff)
    {
        $filter = new QueryFilter();
        $filter->where('createdAt', Operators::LESS_THAN, $cutoff);

        $this->getDetailsRepository()->deleteWhere($filter);
    }

    /**
     * @param DateTime $cutoff
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    private function deleteNotifications(DateTime $cutoff)
    {
        $filter = new QueryFilter();
        $filter->where('createdAt', Operators::LESS_THAN, $cutoff);

        $this->getNotificationsRepository()->deleteWhere($filter);
    }
}