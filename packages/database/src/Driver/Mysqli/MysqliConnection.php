<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Mysqli;

use mysqli;
use Windwalker\Database\Driver\AbstractConnection;

/**
 * The MysqliConnection class.
 */
class MysqliConnection extends AbstractConnection
{
    protected static string $name = 'mysqli';

    /**
     * @var mysqli
     */
    protected mixed $connection = null;

    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return extension_loaded('mysqli');
    }

    public static function getParameters(array $options): array
    {
        return $options;
    }

    protected function doConnect(array $options): bool|mysqli
    {
        mysqli_report(MYSQLI_REPORT_ALL | MYSQLI_REPORT_STRICT);

        return mysqli_connect(
            $options['host'] ?? null,
            $options['user'] ?? null,
            $options['password'] ?? null,
            $options['dbname'] ?? null,
            $options['port'] ?? null,
            $options['socket'] ?? null,
        );
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        if (!$this->isConnected()) {
            return true;
        }

        $r = $this->connection->close();

        $this->connection = null;

        return $r;
    }

    /**
     * @return mysqli
     */
    public function get(): ?mysqli
    {
        return parent::get();
    }
}
