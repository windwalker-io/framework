<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Admin
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

include_once JPATH_ADMINISTRATOR . '/includes/toolbar.php';

/**
 * A Toolbar helper extends from JToolbarHelper.
 *
 * @package     Windwalker.Framework
 * @subpackage  Admin
 * @since       2.5
 */
class AKToolBarHelper
{
	/**
	 * Set admin toolbar title and auto add page title to HTML head document.
	 *
	 * @param   string  $title  Toolbar title.
	 * @param   string  $icon   Icon.
	 *
	 * @return  void
	 */
	public static function title($title, $icon = 'generic.png')
	{
		$doc   = JFactory::getDocument();
		$app   = JFactory::getApplication();
		$input = JFactory::getApplication()->input;

		$doc->setTitle($title);

		$view   = $input->get('view');
		$layout = $input->get('layout', 'default');
		$option = $input->get('option');

		// Strip the extension.
		$icons = explode(' ', $icon);

		$j32icon = '';

		foreach ($icons as &$icon)
		{
			$j32icon .= ' icon-' . preg_replace('#\.[^.]*$#', '', $icon);
			$icon     = 'icon-48-' . preg_replace('#\.[^.]*$#', '', $icon);
		}

		$j32icon = JVERSION >= 3.2 ? '<i class="' . trim($j32icon) . '"></i>' : '';

		$class = "header-{$view}-{$layout}";
		$img   = "components/{$option}/images/admin-icons/{$class}.png";

		if (JFile::exists(JPATH_ADMINISTRATOR . '/' . $img))
		{
			$icon = $class;
		}

		if (JVERSION >= 3)
		{
			$icon = null;
		}

		$admin = $app->isSite() ? JURI::root() . 'administrator/' : '';
		$img   = $admin . "components/{$option}/images/admin-icons/{$class}.png";

		$doc->addStyleDeclaration("
.{$class} {
    background: url({$img}) no-repeat;
}
        ");

		$html = '<div class="pagetitle ' . htmlspecialchars($icon) . '"><h2 class="page-title">' . $j32icon . $title . '</h2></div>';
		//$html = $title ;

		$app->JComponentTitle = $html;
	}

	/**
	 * Set a link button.
	 */
	public static function link($alt, $href = '#', $icon = 'asterisk')
	{
		$bar = JToolbar::getInstance('toolbar');

		// Add a back button.
		$bar->appendButton('Link', $icon, $alt, $href);
	}

	/**
	 * Set a back link button, contain right arrow icon.
	 */
	public static function back($alt = 'JTOOLBAR_BACK', $href = 'javascript:history.back();')
	{
		$bar  = JToolbar::getInstance('toolbar');
		$icon = JVERSION >= 3 ? 'chevron-left' : 'back';

		// Add a back button.
		$bar->appendButton('Link', $icon, $alt, $href);
	}

	/**
	 * Set a modal button.
	 */
	public static function modal($title = 'JTOOLBAR_BATCH', $selector = 'myModal', $icon = 'checkbox-partial')
	{
		AKHelper::_('ui.modal', $selector);
		$bar   = JToolbar::getInstance('toolbar');
		$title = JText::_($title);

		$option = array(
			'class' => 'btn btn-small ' . $selector . '-link',
			'icon'  => JVERSION >= 3 ? 'icon-' . $icon : $icon
		);

		$dhtml = AKHelper::_('ui.modalLink', $title, $selector, $option);
		$bar->appendButton('Custom', $dhtml, 'batch');
	}

	/**
	 * Writes a configuration button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string  $component  The name of the component, eg, com_content.
	 * @param   string  $height     The height of the popup. [UNUSED]
	 * @param   string  $width      The width of the popup. [UNUSED]
	 * @param   string  $alt        The name of the button.
	 * @param   string  $path       An alternative path for the configuation xml relative to JPATH_SITE.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function preferences($component, $height = '550', $width = '875', $alt = 'JToolbar_Options', $path = '')
	{
		$app  = JFactory::getApplication();
		$args = func_get_args();

		$app->triggerEvent('onAKToolbarAppendButton', array('preferences', &$args));
		call_user_func_array(array('JToolBarHelper', 'preferences'), $args);
	}

	/**
	 * If alled method not exists in this class, will auto call JToolbarHelper instead.
	 */
	public static function __callStatic($name, $args)
	{
		$app = JFactory::getApplication();

		$app->triggerEvent('onAKToolbarAppendButton', array($name, &$args));
		call_user_func_array(array('JToolBarHelper', $name), $args);
	}
}
