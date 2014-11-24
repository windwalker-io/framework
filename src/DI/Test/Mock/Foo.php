<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\DI\Test\Mock;

/**
 * The Foo class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Foo
{
	/**
	 * Property bar.
	 *
	 * @var  Bar
	 */
	public $bar = null;

	/**
	 * Class init.
	 *
	 * @param Bar $bar
	 */
	public function __construct(Bar $bar)
	{
		$this->bar = $bar;
	}
}
