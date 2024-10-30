<?php

namespace ChannelEngine\BusinessLogic\InitialSync;

use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Orders\Contracts\OrdersService;
use ChannelEngine\BusinessLogic\Orders\Tasks\MarketplaceOrdersDownloadTask;
use ChannelEngine\BusinessLogic\Orders\Tasks\OrdersDownloadTask;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogService;
use ChannelEngine\BusinessLogic\TransactionLog\Tasks\TransactionalComposite;
use ChannelEngine\Infrastructure\ServiceRegister;

class OrderSync extends TransactionalComposite
{
    /**
     * OrderSync constructor.
     */
    public function __construct()
    {
        parent::__construct($this->getSubTaskList());
    }

    protected function createSubTask($taskKey)
    {
        return new $taskKey;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $transactionLog = $this->getTransactionLog();
        if (!$transactionLog->getTotalCount()) {
            $transactionLog->setTotalCount($this->getOrdersService()->getOrdersCount());
			$this->getLogService()->update($transactionLog);
        }

        if (!count($this->getSubTaskList())) {
            $this->reportProgress(100);
            return;
        }

        parent::execute();
    }

    /**
     * Provides list of tasks.
     *
     * @return array
     */
    protected function getSubTaskList()
    {
        $config = $this->getService()->getOrderSyncConfig();

        if ($config === null || ($config->isEnableOrdersByMarketplaceSync() && $config->isEnableOrdersByMerchantSync())) {
            return [
                MarketplaceOrdersDownloadTask::class => 50,
                OrdersDownloadTask::class => 50
            ];
        }

        if ($config->isEnableOrdersByMerchantSync()) {
            return [OrdersDownloadTask::class => 100];
        }

        if ($config->isEnableOrdersByMarketplaceSync()) {
            return [MarketplaceOrdersDownloadTask::class => 100];
        }

        return [];
    }


    /**
     * Provides orders configuration service.
     *
     * @return OrdersConfigurationService
     */
    protected function getService()
    {
        return ServiceRegister::getService(OrdersConfigurationService::class);
    }

    /**
     * Provides orders service.
     *
     * @return OrdersService
     */
    protected function getOrdersService()
    {
        return ServiceRegister::getService(OrdersService::class);
    }

	/**
	 * Provides transaction log service.
	 *
	 * @return TransactionLogService
	 */
	protected function getLogService()
	{
		return ServiceRegister::getService(TransactionLogService::class);
	}
}