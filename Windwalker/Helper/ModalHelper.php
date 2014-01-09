<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

use JArrayHelper;
use JForm;
use JHtml;
use Windwalker\DI\Container;

// No direct access
defined('_JEXEC') or die;

/**
 * A UI helper to generate modal etc.
 *
 * @since 1.0
 */
class ModalHelper
{
	/**
	 * Set a HTML element as modal container.
	 *
	 * @param   string $selector Modal ID to select element.
	 * @param   array  $option   Modal options.
	 *
	 * @return void
	 */
	public static function modal($selector, $option = array())
	{
		JHtml::_('bootstrap.modal', $selector);
	}

	/**
	 * The link to open modal.
	 *
	 * @param   string  $title    Modal title.
	 * @param   string  $selector Modal select ID.
	 * @param   array   $option   Modal params.
	 *
	 * @return  string  Link body text.
	 */
	public static function modalLink($title, $selector, $option = array())
	{
		$tag     = JArrayHelper::getValue($option, 'tag', 'a');
		$id      = isset($option['id']) ? " id=\"{$option['id']}\"" : "id=\"{$selector}_link\"";
		$class   = isset($option['class']) ? " class=\"{$option['class']} cursor-pointer\"" : 'class="cursor-pointer"';
		$onclick = isset($option['onclick']) ? " onclick=\"{$option['onclick']}\"" : '';
		$icon    = JArrayHelper::getValue($option, 'icon', '');

		$button = "<{$tag} data-toggle=\"modal\" data-target=\"#$selector\"{$id}{$class}{$onclick}>
               <i class=\"{$icon}\" title=\"$title\"></i>
                $title</{$tag}>";

		return $button;
	}

	/**
	 * Put content and render it as modal box HTML.
	 *
	 * @param   string $selector The ID selector for the modal.
	 * @param   string $content  HTML content to put in modal.
	 * @param   array  $option   Optional markup for the modal, footer or title.
	 *
	 * @return  string  HTML markup for a modal
	 *
	 * @since   3.0
	 */
	public static function renderModal($selector = 'modal', $content = '', $option = array())
	{
		self::modal($selector, $option);

		$header = '';
		$footer = '';

		// Header
		if (!empty($option['title']))
		{
			$header = <<<HEADER
<div class="modal-header">
    <button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
    <h3>{$option['title']}</h3>
</div>
HEADER;
		}

		// Footer
		if (!empty($option['footer']))
		{
			$footer = <<<FOOTER
<div class="modal-footer">
    {$option['footer']}
</div>
FOOTER;
		}

		// Box
		$html = <<<MODAL
<div class="modal hide fade {$selector}" id="{$selector}">
{$header}

<div id="{$selector}-container" class="modal-body">
    {$content}
</div>

{$footer}
</div>
MODAL;

		return $html;
	}

	/**
	 * getQuickaddForm
	 */
	static public function getQuickaddForm($id, $path, $extension = null)
	{
		$content = '';

		try
		{
			$form = new JForm($id . '.quickaddform', array('control' => $id));
			$form->loadFile(JPATH_ROOT . '/' . $path);
		}
		catch (\Exception $e)
		{
			$app = Container::getInstance()->get('app');
			$app->enqueueMessage($e->getMessage());

			return false;
		}

		// Set Category Extension
		if ($extension)
		{
			$form->setValue('extension', null, $extension);
		}

		$fieldset = $form->getFieldset('quickadd');

		foreach ($fieldset as $field)
		{
			$content .= "
<div class=\"control-group\" id=\"{$field->id}-wrap\">
	<div class=\"control-label\">
		{$field->label}
	</div>
	<div class=\"controls\">
		{$field->input}
	</div>
</div>";
		}

		return $content;
	}
}
