<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

/**
 * The OdbcConnection class.
 */
class PdoOdbcConnection extends AbstractPdoConnection
{
    /**
     * @var string
     */
    protected static $dbtype = 'odbc';

    public static function getParameters(array $options): array
    {
        $params = [];

        if ($options['driver'] ?? null) {
            $params['Driver'] = $options['driver'];
        }

        if ($options['host'] ?? null) {
            $params['Server'] = $options['host'];
        }

        if ($options['port'] ?? null) {
            $params['Port'] = $options['port'];
        }

        if ($options['database'] ?? null) {
            $params['Database'] = $options['database'];
        }

        $options['dsn'] = static::getDsn($params);

        return $options;
    }
}
