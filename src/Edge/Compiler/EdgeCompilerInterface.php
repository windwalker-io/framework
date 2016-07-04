<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Compiler;

/**
 * The CompilerInterface class.
 *
 * @since  {DEPLOY_VERSION}
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
