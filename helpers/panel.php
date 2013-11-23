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
 * A proxy helper to render tag and slide UI function.
 *     Auto detect Joomla! version, if greater than 3.0, use bootstrap function,
 *     or use Joomla! legacy function.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelperPanel
{
	/**
	 * A legacy for WindWalker 3.2 component.
	 *
	 * @var    boolean
	 * @deprecated  4.0
	 */
	static public $legacy = false;

	/**
	 * Save tab buttons data.
	 *
	 * @var array
	 */
	static public $buttons = array();

	/**
	 * Save tab buttons auto generating script. Will not used in J!3.1.
	 *
	 * @var mixed
	 */
	static public $script = null;

	/**
	 * Start a tabs group, the tab buttons will show in here. Need to echo it.
	 *
	 * @param   string $selector The tabs group ID.
	 * @param   array  $params   Params for tabs.
	 *
	 * @return  string    Tab group start HTML code.
	 */
	public static function startTabs($selector = 'myTab', $params = array())
	{
		if (JVERSION >= 3.1)
		{
			return JHtml::_('bootstrap.startTabSet', $selector, $params);
		}
		elseif (JVERSION >= 3 && JVERSION < 3.1)
		{

			$tab = '';
			$tab = '<ul id="' . $selector . '_buttons" class="nav nav-tabs"></ul>';

			return $tab . JHtml::_('bootstrap.startPane', $selector, $params);
		}
		else
		{
			return JHtml::_('tabs.start', $selector, $params);
		}
	}

	/**
	 * End a tabs group. Need to echo it.
	 */
	public static function endTabs()
	{
		if (JVERSION >= 3.1)
		{
			return JHtml::_('bootstrap.endTabSet');
		}
		elseif (JVERSION >= 3)
		{
			return JHtml::_('bootstrap.endPane');
		}
		else
		{
			return JHtml::_('tabs.end');
		}
	}

	/**
	 * Add a tab panel. Need to echo it.
	 *
	 * @param   string $selector This tabs group ID.
	 * @param   string $text     This tab button name.
	 * @param   string $id       This tab panel ID.
	 *
	 * @return  string    Tab panel HTML.
	 */
	public static function addPanel($selector, $text, $id)
	{
		if (JVERSION >= 3.1)
		{

			return JHtml::_('bootstrap.addTab', $selector, $id, $text);

		}
		elseif (JVERSION >= 3)
		{

			self::$buttons[$selector]['text'] = $text;
			self::$buttons[$selector]['id']   = $id;

			$addclass = !self::$script[$selector] ? ",{class: 'active'}" : '';
			//$ul            = !self::$script[$selector] ? "var btns = $('#{$selector}_buttons') ;\n\n" : '';

			$sc = self::$script[$selector][] = "jQuery('#{$selector}_buttons').append( jQuery('<li>'{$addclass}).append( jQuery('<a>', {'href': '#{$id}', 'data-toggle': 'tab', text: '{$text}' }) ) );";
			echo '<script type="text/javascript">' . $sc . '</script>';

			return JHtml::_('bootstrap.addPanel', $selector, $id, $text);
		}
		else
		{
			return JHtml::_('tabs.panel', $text, $id);
		}
	}

	/**
	 * End a tab panel. Need to echo it.
	 *
	 * @return  HTML
	 */
	public static function endPanel()
	{
		if (JVERSION >= 3.1)
		{
			return JHtml::_('bootstrap.endTab');
		}
		elseif (JVERSION >= 3)
		{
			return JHtml::_('bootstrap.endPanel');
		}
	}

	/**
	 * Start a slider group. Need to echo it.
	 *
	 * @param   string $selector Slider group ID.
	 * @param   array  $params   Slider params.
	 *
	 * @return  string    Slider start HTML.
	 */
	public static function startSlider($selector = 'mySlider', $params = array())
	{
		if (JVERSION >= 3)
		{
			return JHtml::_('bootstrap.startAccordion', $selector, $params);
		}
		else
		{
			return JHtml::_('sliders.start', $selector, $params);
		}
	}

	/**
	 * End a slider group. Need to echo it.
	 *
	 * @return  string Slider end HTML.
	 */
	public static function endSlider()
	{
		if (JVERSION >= 3)
		{
			return JHtml::_('bootstrap.endAccordion');
		}
		else
		{
			return JHtml::_('sliders.end');
		}
	}

	/**
	 * Add a slide panel. Need to echo it.
	 *
	 * @param   string $selector This slider group ID.
	 * @param   string $text     This slide button name.
	 * @param   string $id       This slide panel ID.
	 *
	 * @return  string    Slide panel HTML.
	 */
	public static function addSlide($selector, $text, $id)
	{
		if (JVERSION >= 3)
		{
			return JHtml::_('bootstrap.addSlide', $selector, $text, $id);
		}
		else
		{
			return JHtml::_('sliders.panel', $text, $id);
		}
	}

	/**
	 * End a slide panel. Need to echo it.
	 *
	 * @return  string     End slide HTML.
	 */
	public static function endSlide()
	{
		if (JVERSION >= 3)
		{
			return JHtml::_('bootstrap.endSlide');
		}
	}

	/**
	 * Set Toolbar icon. Not available now.
	 */
	public static function setToolbarIcon($image, $default = 'article.png', $path = 'images/admin-icons')
	{

	}

	/**
	 * Set is legacy mode.
	 *
	 * @param   boolean $conditiotn True or false.
	 */
	public static function setLegacy($conditiotn = true)
	{
		self::$legacy = $conditiotn;
	}
}
