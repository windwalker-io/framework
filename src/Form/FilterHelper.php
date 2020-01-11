<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form;

use Windwalker\Form\Filter\DefaultFilter;

/**
 * The FilterHelper class.
 *
 * @since  2.0
 */
class FilterHelper extends AbstractFormElementHelper
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
    protected static $defaultNamespace = 'Windwalker\\Form\\Filter';

    /**
     * createFilter
     *
     * @param string            $filter
     * @param \SplPriorityQueue $namespaces
     *
     * @return  bool|DefaultFilter
     */
    public static function create($filter, \SplPriorityQueue $namespaces = null)
    {
        if (class_exists((string) $filter)) {
            return new $filter();
        }

        $namespaces = $namespaces ?: static::getNamespaces();

        foreach ($namespaces as $namespace) {
            $class = trim((string) $namespace, '\\') . '\\' . ucfirst($filter) . 'Filter';

            if (class_exists($class)) {
                return new $class();
            }
        }

        return new DefaultFilter($filter);
    }
}
