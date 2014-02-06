<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  {{extension.element.lower}}
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use {{extension.name.cap}}\Component\{{extension.name.cap}}Component as {{extension.name.cap}}ComponentBase;

// No direct access
defined('_JEXEC') or die;

/**
 * Class {{extension.name.cap}}Component
 *
 * @since 1.0
 */
final class {{extension.name.cap}}Component extends {{extension.name.cap}}ComponentBase
{
	/**
	 * Property defaultController.
	 *
	 * @var string
	 */
	protected $defaultController = '{{controller.list.name.lower}}.display';

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
