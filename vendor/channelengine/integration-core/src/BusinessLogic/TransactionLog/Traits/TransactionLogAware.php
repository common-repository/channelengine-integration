<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Traits;

use ChannelEngine\BusinessLogic\TransactionLog\Entities\TransactionLog;

trait TransactionLogAware
{
    /**
     * TransactionLog instance.
     *
     * @var TransactionLog
     */
    protected $transactionLog;

    /**
     * @return TransactionLog
     */
    public function getTransactionLog()
    {
        return $this->transactionLog;
    }

    /**
     * @param TransactionLog $transactionLog
     */
    public function setTransactionLog($transactionLog)
    {
        $this->transactionLog = $transactionLog;
    }
}