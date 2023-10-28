<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

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
     * commit
     *
     * @return  bool
     */
    public function transactionCommit(): bool;

    /**
     * rollback
     *
     * @return  bool
     */
    public function transactionRollback(): bool;
}
