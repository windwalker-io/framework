<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Database\Driver\Pdo\PdoDriver;

/**
 * The SqlsrvDriver class.
 *
 * @since  3.5
 */
class SqlsrvDriver extends PdoDriver
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'sqlsrv';

    /**
     * Is this driver supported.
     *
     * @return  boolean
     */
    public static function isSupported()
    {
        return in_array('sqlsrv', \PDO::getAvailableDrivers(), true);
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
        $options['driver']  = 'sqlsrv';
//        $options['select']  = $options['select'] ?? true;
        $options['charset'] = $options['charset'] ?? 'utf8';

        parent::__construct($connection, $options);
    }
}
