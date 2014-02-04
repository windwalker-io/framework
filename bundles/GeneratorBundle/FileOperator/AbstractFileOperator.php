<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\FileOperator;

use Windwalker\Console\Application\Console;
use Windwalker\Console\Command\Command;

/**
 * Class AbstractFileOperator
 *
 * @since 1.0
 */
class AbstractFileOperator
{
	/**
	 * Property app.
	 *
	 * @var  \Windwalker\Console\Application\Console
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param Console $app
	 */
	public function __construct(Console $app)
	{
		$this->app = $app;
	}
}
