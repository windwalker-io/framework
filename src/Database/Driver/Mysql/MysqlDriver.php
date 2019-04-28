<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Driver\Pdo\PdoDriver;

/**
 * Class MysqlDriver
 *
 * @since 2.0
 */
class MysqlDriver extends PdoDriver
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'mysql';

    /**
     * Is this driver supported.
     *
     * @return  boolean
     */
    public static function isSupported()
    {
        return in_array('mysql', \PDO::getAvailableDrivers(), true);
    }

    /**
     * Constructor.
     *
     * @param   \PDO  $connection The pdo connection object.
     * @param   array $options    List of options used to configure the connection
     *
     * @throws \ReflectionException
     * @since   2.0
     */
    public function __construct(\PDO $connection = null, $options = [])
    {
        $options['driver'] = 'mysql';
        $options['charset'] = $options['charset'] ?? 'utf8';

        parent::__construct($connection, $options);
    }

    /**
     * connect
     *
     * @throws  \RuntimeException
     * @return  static
     */
    public function connect()
    {
        parent::connect();

        $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        return $this;
    }
}
