<?php

namespace ChannelEngine\Infrastructure\TaskExecution;

use ChannelEngine\Infrastructure\Configuration\Configuration;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\Http\HttpClient;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\ORM\Interfaces\RepositoryInterface;
use ChannelEngine\Infrastructure\ORM\QueryFilter\QueryFilter;
use ChannelEngine\Infrastructure\ORM\RepositoryRegistry;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\Singleton;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\Runnable;
use ChannelEngine\Infrastructure\Utility\GuidProvider;
use Exception;

/**
 * Class AsyncProcessStarter.
 *
 * @package ChannelEngine\Infrastructure\TaskExecution
 */
class AsyncProcessStarterService extends Singleton implements AsyncProcessService
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
    /**
     * Configuration instance.
     *
     * @var Configuration
     */
    private $configuration;
    /**
     * Process entity repository.
     *
     * @var RepositoryInterface
     */
    private $processRepository;
    /**
     * GUID provider instance.
     *
     * @var GuidProvider
     */
    private $guidProvider;
    /**
     * HTTP client.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * AsyncProcessStarterService constructor.
     * @throws RepositoryNotRegisteredException
     */
    protected function __construct()
    {
        parent::__construct();

        $this->httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
        $this->guidProvider = ServiceRegister::getService(GuidProvider::CLASS_NAME);
        $this->configuration = ServiceRegister::getService(Configuration::CLASS_NAME);
        $this->processRepository = RepositoryRegistry::getRepository(Process::CLASS_NAME);
    }

    /**
     * Starts given runner asynchronously (in new process/web request or similar).
     *
     * @param Runnable $runner Runner that should be started async.
     *
     * @throws HttpRequestException
     * @throws ProcessStarterSaveException
     */
    public function start(Runnable $runner)
    {
        $guid = trim($this->guidProvider->generateGuid());

        $this->saveGuidAndRunner($guid, $runner);
        $this->startRunnerAsynchronously($guid);
    }

    /**
     * Runs a process with provided identifier.
     *
     * @param string $guid Identifier of process.
     */
    public function runProcess($guid)
    {
        try {
            $filter = new QueryFilter();
            $filter->where('guid', '=', $guid);

            /** @var Process $process */
            $process = $this->processRepository->selectOne($filter);
            if ($process !== null) {
                $process->getRunner()->run();
                $this->processRepository->delete($process);
            }
        } catch (Exception $e) {
            Logger::logError($e->getMessage(), 'Core', ['guid' => $guid]);
        }
    }

    /**
     * Saves runner and guid to storage.
     *
     * @param string $guid Unique process identifier.
     * @param Runnable $runner Runner instance.
     *
     * @throws ProcessStarterSaveException
     */
    private function saveGuidAndRunner($guid, Runnable $runner)
    {
        try {
            $process = new Process();
            $process->setGuid($guid);
            $process->setRunner($runner);

            $this->processRepository->save($process);
        } catch (Exception $e) {
            Logger::logError($e->getMessage());
            throw new ProcessStarterSaveException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Starts runnable asynchronously.
     *
     * @param string $guid Unique process identifier.
     *
     * @throws HttpRequestException
     */
    private function startRunnerAsynchronously($guid)
    {
        try {
            $this->httpClient->requestAsync(
                $this->configuration->getAsyncProcessCallHttpMethod(),
                $this->configuration->getAsyncProcessUrl($guid)
            );
        } catch (Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
            throw new HttpRequestException($e->getMessage(), 0, $e);
        }
    }
}
