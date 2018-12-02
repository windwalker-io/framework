<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 $LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

if (!class_exists('PHPUnit\Framework\TestCase') && class_exists('PHPUnit_Framework_TestCase')) {
    // Split class string to avoid breaking IDE parser from native phpunit compat.
    class_alias('PHPUnit_Framework_TestCase', 'PHPUnit' . '\Framework\TestCase');
}
