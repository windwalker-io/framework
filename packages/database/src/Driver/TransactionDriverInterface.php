<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver;

/**
 * Interface TransactionDriverInterface
 */
interface TransactionDriverInterface
{
    /**
     * start
     *
     * @return  bool
     */
    public function transactionStart(): bool;

    /**
     * @param  bool  $releaseConnection
     *
     * @return  bool
     */
    public function transactionCommit(bool $releaseConnection = true): bool;

    /**
     * rollback
     *
     * @param  bool  $releaseConnection
     *
     * @return  bool
     */
    public function transactionRollback(bool $releaseConnection = true): bool;
}
