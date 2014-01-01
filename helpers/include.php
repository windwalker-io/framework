<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Include CSS & JS helper.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelperInclude
{
	static $bootstrap;
	static $bluestork;
	static $isis;

	/**
	 * A dropdown menu with checkbox to mulit-select.
	 * Use mootools MultiSelect plugin: http://users.skavt.net/blaz/MultiSelect/
	 *
	 * @param   string  $id        The wrap id to select.
	 * @param   boolean $framework Include mootools or not. Use JHtml::_('behavior.framework', true) ;
	 */
	public static function dropdownCheckbox($id = 'MultiSelect', $framework = true)
	{
		if ($framework)
		{
			JHtml::_('behavior.framework', true);
		}

		$doc    = JFactory::getDocument();
		$script = AKHelper::_('path.getWWUrl') . '/assets/js/mootools/multi-select/source/' . (JDEBUG ? 'MultiSelect-uncompressed.js' : 'MultiSelect.js');
		$css    = AKHelper::_('path.getWWUrl') . '/assets/js/mootools/multi-select/css/MultiSelect.css';

		$doc->addScript($script);
		$doc->addStylesheet($css);

		$instance = <<<JS
        window.addEvent('domready', function(){
            var AKMultiSelect_{$id} = new MultiSelect('.{$id}');
        });
JS;

		$doc->addScriptDeclaration($instance);
	}

	/**
	 * Include WindWalker and component core JS.
	 *
	 * @param   boolean $js include core JS or not.
	 */
	public static function core($js = true)
	{
		$doc      = JFactory::getDocument();
		$app      = JFactory::getApplication();
		$option   = JRequest::getVar('option');
		$com_name = str_replace('com_', '', $option);

		$prefix = $app->isAdmin() ? '../' : '';

		if ($js)
		{
			// include Mootools first
			JHtml::_('behavior.framework', true);

			// Include jQuery in Joomla 3.
			if (JVERSION >= 3)
			{
				JHtml::_('jquery.framework', true);
			}

			if ($app->isSite())
			{
				$doc->addScript(AKHelper::_('path.getWWUrl') . '/assets/js/windwalker.js');
				$doc->addScript('components/' . $option . '/includes/js/' . $com_name . '.js');
			}
			else
			{
				$doc->addScript(AKHelper::_('path.getWWUrl') . '/assets/js/windwalker-admin.js');
				$doc->addScript('components/' . $option . '/includes/js/' . $com_name . '-admin.js');
			}
		}
	}

	/**
	 * Include Bootstrap framework from component self.
	 *
	 * @param    boolean $responsive Include responsive CSS.
	 * @param    boolean $js         Include JS.
	 */
	public static function bootstrap($responsive = false, $js = true)
	{
		$doc    = JFactory::getDocument();
		$app    = JFactory::getApplication();
		$option = JRequest::getVar('option');

		$prefix = $app->isSite() ? 'administrator/' : '';
		$min    = JDEBUG ? '.min' : '';

		if (JVERSION < 3)
		{
			JHtml::_('stylesheet', $prefix . 'components/' . $option . '/includes/bootstrap/css/bootstrap' . $min . '.css');
		}
		else
		{
			JHtml::_('bootstrap.loadCss');
		}

		if ($responsive && JVERSION < 3)
		{
			JHtml::_('stylesheet', $prefix . 'components/' . $option . '/includes/bootstrap/css/bootstrap-responsive' . $min . '.css');
		}

		if ($js && JVERSION < 3)
		{
			JHtml::_('script', $prefix . 'components/' . $option . '/includes/bootstrap/js/jquery.js');
			JHtml::_('script', $prefix . 'components/' . $option . '/includes/bootstrap/js/bootstrap' . $min . '.js');

			if (JVERSION < 3)
			{
				$doc->addScriptDeclaration("jQuery.noConflict();");
			}
		}
		else
		{
			JHtml::_('jquery.framework', true, JDEBUG);
			JHtml::_('bootstrap.framework', JDEBUG);
		}

		self::$bootstrap = true;
	}

	/**
	 * Include 2.5 admin Bluestork template CSS. Use for front-end panel.
	 */
	public static function bluestork()
	{
		$doc    = JFactory::getDocument();
		$app    = JFactory::getApplication();
		$option = JRequest::getVar('option');

		$prefix = $app->isSite() ? 'administrator/' : '';

		$doc->addStylesheet($prefix . 'templates/bluestork/css/template.css');

		self::$bluestork = true;
	}

	/*
	 * Include 3.0 admin Isis template CSS. Use for front-end panel.
	 */
	public static function isis()
	{
		if (JVERSION < 3 || self::$isis)
		{
			return;
		}

		$doc    = JFactory::getDocument();
		$app    = JFactory::getApplication();
		$option = JRequest::getVar('option');

		$prefix = $app->isSite() ? 'administrator/' : '';

		JHtml::_('jquery.framework', true);

		$doc->addStylesheet($prefix . 'templates/isis/css/template.css');
		$doc->addScript($prefix . 'templates/isis/js/template.js');

		self::$isis = true;
	}

	/**
	 * Fix some conflict betweean Bootstrap and 2.5 bluestork in front-end panel.
	 */
	public static function fixBootstrapToJoomla()
	{
		$option = JRequest::getVar('option');
		JHtml::_('stylesheet', 'components/' . $option . '/includes/css/fix-bootstrap-to-joomla.css');

		JHtml::_('behavior.framework', true);
		JHtml::_('script', 'components/' . $option . '/includes/js/fix-bootstrap-to-joomla.js', true);
	}

	/**
	 * Include CSS file from different client.
	 * Site & Admin includes from components/com_xxx/includes/css/$path.
	 * WindWalker includes from libraries/windwalker/assets/css/$path.
	 *
	 * @param   string $path   Path & file name from base path.
	 * @param   string $client Client can use: 'site', 'admin', 'administrator', 'ww', 'windwalker'.
	 * @param   string $option Component name.
	 */
	public static function addCSS($path = null, $client = null, $option = null)
	{
		$doc = JFactory::getDocument();

		if ($client == 'windwalker' || $client == 'ww')
		{
			$doc->addStylesheet(JURI::root(true) . '/' . AKHelper::_('uri.windwalker') . '/assets/css/' . $path);
		}
		else
		{
			$doc->addStylesheet(AKHelper::_('uri.component', $client, $option) . '/includes/css/' . $path);
		}
	}

	/**
	 * Include JS file from different client.
	 * Site & Admin includes from components/com_xxx/includes/js/$path.
	 * WindWalker includes from libraries/windwalker/assets/js/$path.
	 *
	 * @param   string $path   Path & file name from base path.
	 * @param   string $client Client can use: 'site', 'admin', 'administrator', 'ww', 'windwalker'.
	 * @param   string $option Component name.
	 */
	public static function addJS($path = null, $client = null, $option = null)
	{
		$doc = JFactory::getDocument();

		if ($client == 'windwalker' || $client == 'ww')
		{
			$doc->addScript(JURI::root(true) . '/' . AKHelper::_('uri.windwalker') . '/assets/js/' . $path);
		}
		else
		{
			$doc->addScript(AKHelper::_('uri.component', $client, $option) . '/includes/js/' . $path);
		}
	}

	/**
	 * Include component CSS files and sort it, only include files which name beginning of number.
	 *
	 * @param   string $path   Path and file name.
	 * @param   string $option Component name.
	 * @param   string $client 'site' , 'admin' or 'administrator'.
	 *
	 * @return  type    returnDesc
	 */
	public static function sortedStyle($path = null, $option = null, $client = null)
	{
		$files = (array) JFolder::files(AKHelper::_('path.get', $client, $option) . '/' . $path, ".css$", true);
		$doc   = JFactory::getDocument();
		$app   = JFactory::getApplication();

		if ($app->isSite())
		{
			$doc->addStylesheet(AKHelper::_('path.getWWUrl') . '/assets/css/windwalker.css');
		}
		else
		{
			$doc->addStylesheet(AKHelper::_('path.getWWUrl') . '/assets/css/windwalker-admin.css');
		}

		foreach ($files as $key => $file)
		{
			$name = explode('-', $file);
			if (is_numeric(array_shift($name)))
			{
				$doc->addStylesheet(AKHelper::_('uri.component', null, $option) . '/' . $path . '/' . $file);
			}
		}
	}

	/**
	 * Include QuickEdit JS.
	 */
	public static function quickedit()
	{
		JHtml::_('behavior.framework', true);
		self::addJS('quickedit.js', 'ww');

		$option = JRequest::getVar('option');
		$view   = JRequest::getVar('view');

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration('AKQuickEdit.init( { "option" : "' . $option . '", "view" : "' . $view . '" } );');
	}
}