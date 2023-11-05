<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

/**
 * The PdoSqliteConnection class.
 */
class PdoSqliteConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'sqlite';

    public static function getParameters(array $options): array
    {
        // If host is default, we should ignore it.
        if ($options['host'] ?? null === 'localhost') {
            unset($options['host']);
        }

        $options['dsn'] ??= static::$dbtype . ':'
            . ($options['host'] ?? $options['file'] ?? $options['dbname'] ?? ':memory:');

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        return parent::disconnect();
    }
}
