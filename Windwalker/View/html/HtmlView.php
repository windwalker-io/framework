<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;

defined('JPATH_PLATFORM') or die;

/**
 * Prototype admin view.
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.2
 */
class HtmlView extends AbstractHtmlView
{
	/**
	 * prepareRender
	 *
	 * @return  void
	 */
	protected function prepareRender()
	{
		parent::prepareRender();

		$this->data->option = $this->option;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since	3.2
	 */
	protected function addToolbar()
	{
	}

	/**
	 * Add the submenu.
	 *
	 * @return  void
	 *
	 * @since	3.2
	 */
	protected function addSubmenu()
	{
	}


}
