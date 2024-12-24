<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

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
        $params = array_filter($params);

        $dsn = [];

        foreach ($params as $key => $value) {
            $dsn[] = $key . '=' . $value;
        }

        $dsn = implode($delimiter, $dsn);

        if ($dbtype) {
            $dsn = $dbtype . ':' . $dsn;
        }

        return $dsn;
    }
}
