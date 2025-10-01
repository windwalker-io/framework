<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test\Stub;

use Windwalker\Pool\AbstractConnection;

/**
 * The StubConnection class.
 */
class StubConnection extends AbstractConnection
{
    protected string $connection = '';

    /**
     * @inheritDoc
     */
    public function connect(): mixed
    {
        $this->connection = 'Hello';

        return $this->connection;
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        $this->connection = '';

        return '';
    }

    /**
     * @inheritDoc
     */
    public function isConnected(): bool
    {
        return $this->connection === 'Hello';
    }

    public function ping(): bool
    {
        return true;
    }
}
