<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form;

use Windwalker\Validator\Rule\NoneValidator;
use Windwalker\Validator\Rule\RegexValidator;
use Windwalker\Validator\ValidatorInterface;

/**
 * The ValidatorHelper class.
 *
 * @since  2.0
 */
class ValidatorHelper extends AbstractFormElementHelper
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
    protected static $defaultNamespace = 'Windwalker\\Validator\\Rule';

    /**
     * createRule
     *
     * @param string            $rule
     * @param \SplPriorityQueue $namespaces
     *
     * @return  ValidatorInterface
     */
    public static function create($rule, \SplPriorityQueue $namespaces = null)
    {
        if (!$rule) {
            return new NoneValidator();
        }

        if (class_exists($rule)) {
            return new $rule();
        }

        $namespaces = $namespaces ?: static::getNamespaces();

        foreach ($namespaces as $namespace) {
            $class = $namespace . '\\' . ucfirst($rule) . 'Validator';

            if (class_exists($class)) {
                return new $class();
            }
        }

        if (is_string($rule)) {
            return new RegexValidator($rule);
        }

        throw new \InvalidArgumentException(sprintf('Validator %s is not exists.', $rule));
    }
}
