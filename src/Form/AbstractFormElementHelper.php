<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form;

/**
 * The AbstractFormElemetAwareHelper class.
 *
 * @since  2.0
 */
abstract class AbstractFormElementHelper
{
    /**
     * Property fieldNamespaces.
     *
     * @var  \SplPriorityQueue
     */
    protected static $namespaces = null;

    /**
     * Property defaultNamespace.
     *
     * @var string
     */
    protected static $defaultNamespace;

    /**
     * init
     *
     * @param boolean $reset
     *
     * @return  void
     */
    public static function init($reset = false)
    {
        if (!static::$namespaces || $reset) {
            $namespaces = new \SplPriorityQueue();

            $namespaces->insert(static::$defaultNamespace, $reset);

            static::$namespaces = $namespaces;
        }
    }

    /**
     * createField
     *
     * @param string            $name
     * @param \SplPriorityQueue $namespaces
     *
     * @throws \InvalidArgumentException
     *
     * @return  mixed
     */
    public static function create($name, \SplPriorityQueue $namespaces = null)
    {
        throw new \LogicException('Please override this method.');
    }

    /**
     * reset
     *
     * @return  void
     */
    public static function reset()
    {
        static::init(true);
    }

    /**
     * addNamespace
     *
     * @param string $namespace
     * @param int    $priority
     *
     * @return  void
     */
    public static function addNamespace($namespace, $priority = 256)
    {
        static::init();

        static::$namespaces->insert($namespace, $priority);
    }

    /**
     * getNamespaces
     *
     * @return  \SplPriorityQueue
     */
    public static function getNamespaces()
    {
        static::init();

        return static::$namespaces;
    }
}
