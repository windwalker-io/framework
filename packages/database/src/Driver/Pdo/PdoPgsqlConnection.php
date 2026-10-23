<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Driver\DriverOptions;

/**
 * The PdoPgsqlConnection class.
 */
class PdoPgsqlConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'pgsql';

    public static function prepareDbOptions(DriverOptions $options): DriverOptions
    {
        $params['host'] = $options->host;
        $params['port'] = $options->port ?? null;
        $params['dbname'] = $options->dbname ?? null;
        $params['charset'] = $options->charset ?? null;

        if ($options->tls !== null) {
            $params['sslkey'] = $options->tls['key'] ?? null;
            $params['sslcert'] = $options->tls['cert'] ?? null;
            $params['sslrootcert'] = $options->tls['ca'] ?? null;
            $params['sslcrl'] = $options->tls['capath'] ?? null;
            $params['sslpassword'] = $options->tls['cipherAlgos'] ?? null;

            if (isset($options->tls['verify'])) {
                $params['sslmode'] = $options->tls['verify'] ? 'verify-full' : 'require';
            }
        }

        $options->dsn ??= static::getDsn($params);

        return $options;
    }
}
