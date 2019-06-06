<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace {

    use Windwalker\Utilities\ArrayHelper;

    if (!function_exists('with')) {
        /**
         * Return the given object. Useful for chaining. This class has many influences from Joomla!Framework:
         * https://github.com/joomla/joomla-framework/blob/staging/src/Joomla/PHP/methods.php
         *
         * This method provides forward compatibility for the PHP 5.4 feature Class member access on instantiation.
         * e.g. (new Foo)->bar().
         * See: http://php.net/manual/en/migration54.new-features.php
         *
         * @param   mixed $object The object to return.
         *
         * @since  2.0
         *
         * @return mixed
         *
         * @deprecated Use native syntax.
         */
        function with($object)
        {
            return $object;
        }
    }

    if (!function_exists('show')) {
        /**
         * Dump Array or Object as tree node. If send multiple params in this method, this function will batch print it.
         *
         * @param   mixed $args Array or Object to dump.
         *
         * @since   2.0
         *
         * @return  void
         */
        function show(...$args)
        {
            $last = $args[array_key_last($args)];

            $level = is_int($last) ? $last : 5;

            echo "\n\n";

            if (PHP_SAPI !== 'cli') {
                echo '<pre>';
            }

            // Dump Multiple values
            if (count($args) > 1) {
                $prints = [];

                $i = 1;

                foreach ($args as $arg) {
                    $prints[] = '[Value ' . $i . "]\n" . ArrayHelper::dump($arg, $level);
                    $i++;
                }

                echo implode("\n\n", $prints);
            } else {
                // Dump one value.
                echo ArrayHelper::dump($args[0], $level);
            }

            if (PHP_SAPI !== 'cli') {
                echo '</pre>';
            }
        }
    }

    if (!function_exists('is_stringable')) {
        /**
         * is_stringable
         *
         * @param mixed $var
         *
         * @return  bool
         *
         * @since  3.5
         */
        function is_stringable($var): bool
        {
            return (is_scalar($var) && !is_bool($var)) || (is_object($var) && method_exists($var, '__toString'));
        }
    }
}

namespace Windwalker {
    /**
     * Do some operation after value get.
     *
     * @param mixed    $value
     * @param callable $callable
     *
     * @return  mixed
     *
     * @since  3.5.1
     */
    function tap($value, callable $callable)
    {
        $callable($value);

        return $value;
    }
}
