<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Pdo;

/**
 * Class PdoHelper
 *
 * @see   http://php.net/manual/en/pdo.drivers.php
 *
 * @since 2.0
 */
class PdoHelper
{
    /**
     * Property options.
     *
     * @var  array
     */
    private static $options = [];

    /**
     * extractDsn
     *
     * @param   string $dsn
     *
     * @return  array
     */
    public static function extractDsn($dsn)
    {
        // Parse DSN to array
        $dsn = str_replace(';', "\n", $dsn);
        $dsn = parse_ini_string($dsn);

        return $dsn;
    }

    /**
     * getDsn
     *
     * @param string $driver
     * @param array  $options
     *
     * @throws  \DomainException
     * @return  string
     */
    public static function getDsn($driver, $options = [])
    {
        self::$options = $options;

        if (!is_callable([get_called_class(), $driver])) {
            throw new \DomainException('The ' . $driver . ' driver is not supported.');
        }

        list($dsn, $replace) = static::$driver();

        $dsn = strtr($dsn, $replace);

        self::$options = [];

        return $dsn;
    }

    /**
     * cubrid
     *
     * @return  array
     */
    protected static function cubrid()
    {
        return [
            'cubrid:host={HOST};port={PORT};dbname={DBNAME}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{PORT}' => static::getOption('port', 33000),
                '{DBNAME}' => static::getOption('database'),
            ],
        ];
    }

    /**
     * dblib
     *
     * @return  array
     */
    protected static function dblib()
    {
        return [
            'dblib:host={HOST};port={PORT};dbname={DBNAME}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{PORT}' => static::getOption('port', 1433),
                '{DBNAME}' => static::getOption('database'),
            ],
        ];
    }

    /**
     * firebird
     *
     * @return  array
     */
    protected static function firebird()
    {
        return [
            'firebird:dbname={DBNAME}',
            [
                '{DBNAME}' => static::getOption('database'),
            ],
        ];
    }

    /**
     * ibm
     *
     * @return  array
     */
    protected static function ibm()
    {
        if ($dsn = static::getOption('dsn')) {
            return [
                'ibm:DSN={DSN}',
                [
                    '{DSN}' => $dsn,
                ],
            ];
        }

        return [
            'ibm:hostname={HOST};port={PORT};database={DBNAME}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{PORT}' => static::getOption('port', 56789),
                '{DBNAME}' => static::getOption('database'),
            ],
        ];
    }

    /**
     * dblib
     *
     * @return  array
     */
    protected static function informix()
    {
        if ($dsn = static::getOption('dsn')) {
            return [
                'informix:DSN={DSN}',
                [
                    '{DSN}' => $dsn,
                ],
            ];
        }

        return [
            'informix:host={HOST};service={PORT};database={DBNAME};server={SERVER};protocol={PROTOCOL}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{PORT}' => static::getOption('port', 1526),
                '{DBNAME}' => static::getOption('database'),
                '{SERVER}' => static::getOption('server'),
                '{PROTOCOL}' => static::getOption('protocol'),
            ],
        ];
    }

    /**
     * mssql
     *
     * @return  array
     */
    protected static function mssql()
    {
        return [
            'mssql:host={HOST};port={PORT};dbname={DBNAME}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{PORT}' => static::getOption('port', 1433),
                '{DBNAME}' => static::getOption('database'),
            ],
        ];
    }

    /**
     * mysql
     *
     * @return  array
     */
    protected static function mysql()
    {
        return [
            'mysql:host={HOST};port={PORT};dbname={DBNAME};charset={CHARSET}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{PORT}' => static::getOption('port', 3306),
                '{DBNAME}' => static::getOption('database'),
                '{CHARSET}' => static::getOption('charset', 'utf8'),
            ],
        ];
    }

    /**
     * oci
     *
     * @return  array
     */
    protected static function oci()
    {
        if ($dsn = static::getOption('dsn')) {
            return [
                'oci:dbname={DSN};charset={CHARSET}',
                [
                    '{DSN}' => $dsn,
                    '{CHARSET}' => static::getOption('charset', 'AL32UTF8'),
                ],
            ];
        }

        return [
            'oci:dbname=//#HOST#:#PORT#/#DBNAME};charset={CHARSET}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{PORT}' => static::getOption('port', 56789),
                '{DBNAME}' => static::getOption('database'),
                '{CHARSET}' => static::getOption('charset', 'AL32UTF8'),
            ],
        ];
    }

    /**
     * odbc
     *
     * @return  array
     */
    protected static function odbc()
    {
        return [
            'odbc:DSN={DSN};UID:#USER};PWD={PASSWORD}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{USER}' => static::getOption('user', 1433),
                '{PASSWORD}' => static::getOption('password'),
            ],
        ];
    }

    /**
     * pgsql
     *
     * @return  array
     */
    protected static function pgsql()
    {
        return [
            'pgsql:host={HOST};port={PORT};dbname={DBNAME}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{PORT}' => static::getOption('port', 5432),
                '{DBNAME}' => static::getOption('database', 'postgres'),
            ],
        ];
    }

    /**
     * Alias of pgsql
     *
     * @return  array
     */
    protected static function postgresql()
    {
        return static::pgsql();
    }

    /**
     * sqlite
     *
     * @return  array
     */
    protected static function sqlite()
    {
        $version = static::getOption('version');

        if ($version == 2) {
            $format = 'sqlite2:#DBNAME}';
        } else {
            $format = 'sqlite:#DBNAME}';
        }

        return [
            $format,
            [
                '{DBNAME}' => static::getOption('database'),
            ],
        ];
    }

    /**
     * sybase
     *
     * @return  array
     */
    protected static function sybase()
    {
        return [
            'pgsql:host={HOST};port={PORT};dbname={DBNAME}',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{PORT}' => static::getOption('port', 1433),
                '{DBNAME}' => static::getOption('database'),
            ],
        ];
    }

    /**
     * sybase
     *
     * @return  array
     */
    protected static function fourd()
    {
        return [
            '4D:host={HOST};charset=UTF-8',
            [
                '{HOST}' => static::getOption('host', 'localhost'),
                '{CHARSET}' => static::getOption('charset', 'UTF-8'),
            ],
        ];
    }

    /**
     * getOption
     *
     * @param string $name
     * @param string $default
     *
     * @return  mixed
     */
    protected static function getOption($name, $default = null)
    {
        return isset(self::$options[$name]) ? self::$options[$name] : $default;
    }
}
