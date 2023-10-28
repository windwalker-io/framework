<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Pool\Test\Stub;

use Windwalker\Pool\AbstractPool;
use Windwalker\Pool\ConnectionInterface;

/**
 * The StubConnectionPool class.
 */
class StubConnectionPool extends AbstractPool
{
    /**
     * @inheritDoc
     */
    protected function create(): ConnectionInterface
    {
        return new StubConnection();
    }
}
