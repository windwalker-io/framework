<?php

declare(strict_types=1);

namespace Windwalker\Database\Platform;

enum DbPlatform
{
    case MySQL;
    case PostgreSQL;
    case SQLite;
    case SQLServer;

    public static function wrap(string|self $name): self
    {
        if ($name instanceof self) {
            return $name;
        }

        return match (strtolower($name)) {
            'mysql', 'mariadb' => self::MySQL,
            'pgsql', 'postgresql' => self::PostgreSQL,
            'sqlite' => self::SQLite,
            'sqlsrv', 'sqlserver' => self::SQLServer,
            default => throw new \InvalidArgumentException("Unknown database platform: $name"),
        };
    }
}
