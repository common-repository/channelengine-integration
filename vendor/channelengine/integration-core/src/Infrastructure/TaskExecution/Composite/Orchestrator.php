<?php

namespace ChannelEngine\Infrastructure\TaskExecution\Composite;

use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\Serializer\Serializer;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\Priority;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;
use ChannelEngine\Infrastructure\TaskExecution\Task;
use InvalidArgumentException;

/**
 * Class Orchestrator
 *
 * @package ChannelEngine\Infrastructure\TaskExecution\Composite
 */
abstract class Orchestrator extends Task
{
    const QUEUE_NAME_PREFIX = 'SUB_JOB_';

    /**
     * List of subtasks created and managed by the orchestrator
     *
     * @var ExecutionDetails[]
     */
    protected $taskList = [];

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        $taskList = [];
        foreach ($this->taskList as $data) {
            $taskList[] = Serializer::serialize($data);
        }

        return Serializer::serialize(['taskList' => $taskList]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $data = Serializer::unserialize($serialized);
        foreach ($data['taskList'] as $item) {
            $this->taskList[] = Serializer::unserialize($item);
        }
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        $entity = new static();
        foreach ($array['taskList'] as $data)
        {
            $entity->taskList[] = ExecutionDetails::fromArray($data);
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $taskList = [];
        foreach ($this->taskList as $data) {
            $taskList[] = $data->toArray();
        }

        return ['taskList' => $taskList];
    }

    /**
     * Creates subtasks.
     *
     * @final
     */
    public function execute()
    {
        while ($task = $this->getSubTask()) {
            $this->taskList[] = $task;

            $this->reportAlive();
        }

        if (empty($this->taskList)) {
            $this->reportProgress(100);

            return;
        }

        $this->startSubJobs();
        $this->reportAlive(true);
    }

    /**
     * Update progress of a sub-job.
     *
     * @param int $executionId Sub-Job id.
     * @param int $progress Between 0 and 100, inclusive.
     *
     * @final
     */
    public function updateSubJobProgress($executionId, $progress)
    {
        if ($progress > 100 || $progress < 0) {
            throw new InvalidArgumentException("Invalid progress {$progress} provided. ");
        }

        if (!($subJob = $this->getSubJob($executionId))) {
            throw new InvalidArgumentException("Provided execution with id {$executionId} not found in task list");
        }

        $subJob->setProgress($progress);
        $this->reportProgress($this->calculateProgress());
    }

    /**
     * @inheritDoc
     * @final
     */
    public function onFail()
    {
        $this->abortSubJobs();
    }

    /**
     * @inheritDoc
     * @final
     */
    public function onAbort()
    {
        $this->abortSubJobs();
    }

    /**
     * Provides next available subtask. When no subtasks are available for creation provides null.
     *
     * @return ExecutionDetails | null
     */
    abstract protected function getSubTask();

    /**
     * Creates sub-job.
     *
     * @param Task $task
     * @param int $weight
     * @return ExecutionDetails
     *
     * @throws QueueStorageUnavailableException
     */
    protected function createSubJob(Task $task, $weight = 1)
    {
        $queueItem = $this->getQueueService()->create($this->getSubJobQueueName(), $task, $this->getContext(), Priority::NORMAL, $this->getExecutionId());

        return new ExecutionDetails($queueItem->getId(), $weight);
    }

    /**
     * Calculates progress using the wighted average approach.
     *
     * @return float
     */
    private function calculateProgress()
    {
        $totalWeight = 0;
        $totalProgress = 0;

        foreach ($this->taskList as $taskDetails) {
            $totalWeight += $taskDetails->getWeight();
            $totalProgress += $taskDetails->getProgress() * $taskDetails->getWeight();
        }

        return $totalProgress / $totalWeight;
    }

    /**
     * Provides sub-job queue name.
     *
     * @return string
     */
    protected function getSubJobQueueName()
    {
        return static::QUEUE_NAME_PREFIX . $this->getExecutionId();
    }

    /**
     * Provides queue service.
     *
     * @return QueueService
     */
    private function getQueueService()
    {
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }

    /**
     * Provides current context.
     *
     * @return string
     */
    private function getContext()
    {
        /** @var ConfigurationManager $configManager */
        $configManager = ServiceRegister::getService(ConfigurationManager::class);

        return $configManager->getContext();
    }

    /**
     * Retrieves sub job by execution id.
     *
     * @param $executionId
     *
     * @return ExecutionDetails | false
     */
    private function getSubJob($executionId)
    {
        return current(array_filter(
            $this->taskList,
            static function (ExecutionDetails $d) use ($executionId) {
                return $d->getExecutionId() === $executionId;
            }
        ));
    }

    /**
     * Aborts incomplete sub-jobs.
     */
    private function abortSubJobs()
    {
        $ids = [];
        foreach ($this->taskList as $task) {
            if ($task->getProgress() < 100) {
                $ids[] = $task->getExecutionId();
            }
        }

        if (empty($ids)) {
            return;
        }

        $this->getQueueService()->batchStatusUpdate($ids, QueueItem::ABORTED);
    }

    /**
     * Starts sub-jobs.
     */
    private function startSubJobs()
    {
        $ids = array_map(static function (ExecutionDetails $d) {return $d->getExecutionId();}, $this->taskList);
        if (empty($ids)) {
            return;
        }

        $this->getQueueService()->batchStatusUpdate($ids, QueueItem::QUEUED);
    }
}