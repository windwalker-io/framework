<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_flower
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Flower\Component\FlowerComponent as FlowerComponentBase;

// No direct access
defined('_JEXEC') or die;

/**
 * Class FlowerComponent
 *
 * @since 1.0
 */
final class FlowerComponent extends FlowerComponentBase
{
	/**
	 * Property defaultController.
	 *
	 * @var string
	 */
	protected $defaultController = 'sakuras.display';

	/**
	 * init
	 *
	 * @return void
	 */
	protected function prepare()
	{
		parent::prepare();
	}
}
