<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database;

use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Database\Driver\DatabaseDriverInterface;

/**
 * Class DatabaseFactory
 */
abstract class DatabaseFactory
{
    /**
     * The default DB object.
     *
     * @var AbstractDatabaseDriver
     */
    protected static $db = null;

    /**
     * Property instances.
     *
     * @var  array
     */
    protected static $instances = [];

    /**
     * getDbo
     *
     * @param string $driver
     * @param array  $option
     * @param bool   $forceNew
     *
     * @throws \InvalidArgumentException
     * @return  AbstractDatabaseDriver
     */
    public static function getDbo($driver = null, $option = [], $forceNew = false)
    {
        // No driver name given, we return default DB object.
        if (!$driver) {
            return self::$db;
        }

        // Create new instance if this driver not exists.
        if (empty(self::$instances[$driver]) || $forceNew) {
            self::$instances[$driver] = static::createDbo($driver, $option);

            // Set default DB object.
            if (!self::$db) {
                self::$db = self::$instances[$driver];
            }
        }

        return self::$instances[$driver];
    }

    /**
     * setDbo
     *
     * @param string                 $driver
     * @param AbstractDatabaseDriver $db
     *
     * @return  void
     */
    public static function setDbo($driver, AbstractDatabaseDriver $db = null)
    {
        self::$instances[$driver] = $db;
    }

    /**
     * setDb
     *
     * @param   AbstractDatabaseDriver $db
     *
     * @return  void
     */
    public static function setDefaultDbo(AbstractDatabaseDriver $db = null)
    {
        self::$db = $db;

        if ($db) {
            $driver = $db->getName();

            self::$instances[$driver] = $db;
        }
    }

    /**
     * createDbo
     *
     * @param string $driver
     * @param array  $options
     *
     * @throws \RuntimeException
     * @return  AbstractDatabaseDriver
     */
    public static function createDbo($driver, array $options)
    {
        // Sanitize the database connector options.
        $options['driver'] = preg_replace('/[^A-Z0-9_\.-]/i', '', $driver);
        $options['database'] = $options['database'] ?? null;
        $options['select'] = $options['select'] ?? true;

        // Use custom Resource
        $resource = $options['resource'] ?? null;

        // Derive the class name from the driver.
        $class = '\\Windwalker\\Database\\Driver\\' . ucfirst(strtolower($options['driver'])) . '\\'
            . ucfirst(strtolower($options['driver'])) . 'Driver';

        // If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf('Unable to load Database Driver: %s', $options['driver']));
        }

        /** @var DatabaseDriverInterface $class */
        if (!$class::isSupported()) {
            throw new \RangeException('Database driver ' . $driver . ' not supported.');
        }

        // Create our new Driver connector based on the options given.
        try {
            $instance = new $class($resource, $options);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to connect to the Database: %s', $e->getMessage()));
        }

        return $instance;
    }

    /**
     * reset
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function reset(): void
    {
        static::$instances = [];
        static::$db = null;
    }
}
