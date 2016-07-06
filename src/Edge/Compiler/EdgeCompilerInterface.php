<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Compiler;

/**
 * The CompilerInterface class.
 *
 * @since  3.0-beta2
 */
interface EdgeCompilerInterface
{
	/**
	 * compile
	 *
	 * @param   string  $value
	 *
	 * @return  string
	 */
	public function compile($value);

	/**
	 * Register a handler for custom directives.
	 *
	 * @param  string  $name
	 * @param  callable  $handler
	 * @return static
	 */
	public function directive($name, $handler);

	/**
	 * parser
	 *
	 * @param   callable  $handler
	 *
	 * @return  static
	 */
	public function parser($handler);
}
