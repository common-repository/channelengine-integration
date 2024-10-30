<?php

namespace ChannelEngine\BusinessLogic\Returns\Tasks;

use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\BusinessLogic\API\Returns\DTO\ReturnResponse;
use ChannelEngine\BusinessLogic\API\Returns\DTO\ReturnsPage;
use ChannelEngine\BusinessLogic\API\Returns\Http\Proxy;
use ChannelEngine\BusinessLogic\Returns\Configuration\ReturnsConfigurationService;
use ChannelEngine\BusinessLogic\Returns\Contracts\ReturnsService;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\Serializer\Serializer;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Task;
use ChannelEngine\Infrastructure\Utility\TimeProvider;
use DateTime;

/**
 * Class ReturnSync
 *
 * @package ChannelEngine\BusinessLogic\Returns\Tasks
 */
class ReturnSync extends Task
{
    /**
     * @var int
     */
    private $page;

    /**
     * @param int $page
     */
    public function __construct($page = 1)
    {
        $this->page = $page;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return ['page' => $this->page];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        $task = new self();
        $task->page = $array['page'];

        return $task;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $unserialized = Serializer::unserialize($serialized);
        $this->page = $unserialized['page'];
    }

    /**
     * @inheritDoc
     *
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     * @throws RequestNotSuccessfulException
     */
    public function execute()
    {
        $executionSyncedReturns = 0;
        $lastSyncTime = $this->getLastSyncTime();

        while (true) {
            $returnsPage = $this->getReturnsPage($lastSyncTime);
            $returns = $returnsPage->getReturns();
            $totalCount = $returnsPage->getTotalCount();

            if (empty($returns)) {
                break;
            }

            foreach ($returns as $index => $return) {
                $executionSyncedReturns++;
                $this->createReturnInShop($return);

                if (($index % 10) === 0) {
                    $this->reportProgress($this->getCurrentProgress($totalCount ?: 1, $executionSyncedReturns));
                }
            }

            $this->page++;
        }

        $this->setLastSyncTime($this->getTimeProvider()->getCurrentLocalTime());
        $this->reportProgress(100);
    }

    /**
     * @param int $totalCount
     * @param int $executionSyncedReturns
     *
     * @return float | int
     */
    protected function getCurrentProgress($totalCount, $executionSyncedReturns)
    {
        return ($executionSyncedReturns * 99.0) / $totalCount;
    }

    /**
     * @param ReturnResponse $return
     *
     * @return void
     */
    protected function createReturnInShop(ReturnResponse $return)
    {
        $this->getReturnsService()->createInShop($return);
    }

    /**
     * @param DateTime $lastSyncTime
     *
     * @return ReturnsPage
     *
     * @throws QueryFilterInvalidParamException
     * @throws RequestNotSuccessfulException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    protected function getReturnsPage(DateTime $lastSyncTime)
    {
        return $this->getProxy()->getReturns($lastSyncTime, $this->page);
    }

    /**
     * @return DateTime
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    protected function getLastSyncTime()
    {
        return $this->getReturnsConfigService()->getReturnsLastSyncTime();
    }

    /**
     * @param $lastSyncTime
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    protected function setLastSyncTime($lastSyncTime)
    {
        $this->getReturnsConfigService()->setReturnsLastSyncTime($lastSyncTime);
    }

    /**
     * @return ReturnsConfigurationService
     */
    protected function getReturnsConfigService()
    {
        return ServiceRegister::getService(ReturnsConfigurationService::class);
    }

    /**
     * @return ReturnsService
     */
    protected function getReturnsService()
    {
        return ServiceRegister::getService(ReturnsService::class);
    }

    /**
     * @return Proxy
     */
    protected function getProxy()
    {
        return ServiceRegister::getService(Proxy::class);
    }

    /**
     * @return TimeProvider
     */
    protected function getTimeProvider()
    {
        return ServiceRegister::getService(TimeProvider::class);
    }
}