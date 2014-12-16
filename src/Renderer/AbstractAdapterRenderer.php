<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Renderer;

/**
 * The AbstractAdapterRenderer class.
 * 
 * @since  2.0
 */
abstract class AbstractAdapterRenderer extends AbstractRenderer
{
	/**
	 * Property engine.
	 *
	 * @var  object
	 */
	protected $engine = null;

	/**
	 * Method to get property Engine
	 *
	 * @param   boolean $new
	 *
	 * @return  object
	 */
	abstract public function getEngine($new = false);

	/**
	 * Method to set property engine
	 *
	 * @param   object $engine
	 *
	 * @return  static  Return self to support chaining.
	 */
	abstract public function setEngine($engine);
}
