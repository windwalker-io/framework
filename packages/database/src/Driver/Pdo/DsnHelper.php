<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use InvalidArgumentException;

/**
 * The PdoHelper class.
 */
class DsnHelper
{
    /**
     * @param  string       $dsn
     * @param  string|null  $type
     *
     * @return  array
     */
    public static function extract(string $dsn, ?string &$type = null): array
    {
        if (str_contains($dsn, ':')) {
            [$type, $dsn] = explode(':', $dsn);
        }

        // Parse DSN to array
        $dsn = str_replace(';', "\n", $dsn);

        $values = [];

        foreach (explode("\n", $dsn) as $value) {
            [$k, $v] = explode('=', trim($value));

            $values[$k] = $v;
        }

        return $values;
    }

    public static function build(array $params, ?string $dbtype = null, string $delimiter = ';'): string
    {
        if (!in_array($delimiter, [';', ' '], true)) {
            throw new InvalidArgumentException('Unsupported DSN delimiter.');
        }

        $dsn = [];

        foreach ($params as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (!preg_match('/\A[A-Za-z_][A-Za-z0-9_]*\z/', (string) $key)) {
                throw new InvalidArgumentException('Invalid DSN parameter name.');
            }

            $value = (string) $value;

            if (str_contains($value, "\0") || ($delimiter === ';' && str_contains($value, ';'))) {
                throw new InvalidArgumentException('DSN parameter contains an unsupported character.');
            }

            // PDO pgsql converts semicolon separators to libpq conninfo separators.
            if ($delimiter === ' ' || $dbtype === 'pgsql') {
                $value = "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
            }

            $dsn[] = $key . '=' . $value;
        }

        $dsn = implode($delimiter, $dsn);

        if ($dbtype) {
            $dsn = $dbtype . ':' . $dsn;
        }

        return $dsn;
    }
}
