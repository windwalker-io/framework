<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $LYRASOFT.
 * @license    __LICENSE__
 */

if (!class_exists('PHPUnit\Framework\TestCase') && class_exists('PHPUnit_Framework_TestCase'))
{
	class_alias('PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase');
}
