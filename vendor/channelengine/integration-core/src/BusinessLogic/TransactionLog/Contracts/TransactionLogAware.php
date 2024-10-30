<?php

namespace ChannelEngine\BusinessLogic\TransactionLog\Contracts;

use ChannelEngine\BusinessLogic\TransactionLog\Entities\TransactionLog;

interface TransactionLogAware
{
    /**
     * Provides transaction log.
     *
     * @return TransactionLog
     */
    public function getTransactionLog();

    /**
     * Sets transaction log.
     *
     * @param TransactionLog $transactionLog
     */
    public function setTransactionLog($transactionLog);
}