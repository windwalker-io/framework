<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action;

use CodeGenerator\Action\Action;
use Windwalker\DI\Container;

/**
 * Class AbstractAction
 *
 * @since 1.0
 */
abstract class AbstractAction extends Action
{
	/**
	 * Contructor.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container = null)
	{
		$this->container = $container ? : Container::getInstance();
	}
}
