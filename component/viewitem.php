<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Component
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

include_once dirname(__FILE__) . '/view.php';

/**
 * View class for Item and Edit.
 *
 * @package     Windwalker.Framework
 * @subpackage  Component
 */
class AKViewItem extends AKView
{
	/**
	 * Item cache.
	 *
	 * @var array
	 */
	protected $item = null;

	/**
	 * Pagination cache.
	 *
	 * @var object
	 */
	protected $pagination = null;

	/**
	 * Model state.
	 *
	 * @var JRegistry
	 */
	protected $state = null;

	/**
	 * Component option name.
	 *
	 * @var string
	 */
	protected $option = '';

	/**
	 * List name.
	 *
	 * @var string
	 */
	protected $list_name = '';

	/**
	 * Item name.
	 *
	 * @var string
	 */
	protected $item_name = '';

	/**
	 * Display this view, if in front-end, will show toolbar and submenus.
	 *
	 * @param   string  $tpl   View layout name.
	 * @param   type    $path  The panel layout from?
	 *
	 * @return  string    Render result.
	 */
	public function displayWithPanel($tpl = null, $path = null)
	{
		$app = JFactory::getApplication();

		$this->addToolbar();
		$this->handleFields();

		// If is frontend, show toolbar
		if ($app->isAdmin())
		{
			parent::display($tpl);
		}
		else
		{
			parent::displayWithPanel($tpl, $path);
		}
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		JToolBarHelper::apply($this->item_name . '.apply');
		JToolBarHelper::save($this->item_name . '.save');
		JToolBarHelper::custom($this->item_name . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		JToolBarHelper::custom($this->item_name . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		JToolBarHelper::cancel($this->item_name . '.cancel');
	}

	/**
	 * Handle fields before render Form.
	 *
	 * @return void
	 */
	public function handleFields()
	{
		/** @var $form \JForm */
		$form = $this->form;

		// If not Nested item, hide parent_id.
		if (!$this->state->get('item.nested'))
		{
			if ($form->getField('parent_id', 'basic'))
			{
				$form->removeField('parent_id', 'basic');
			}
		}
	}

	/**
	 * A method to render fieldset tabs or slider.
	 *
	 * @param   object $fieldset
	 * @param   array  $fieldsets
	 *
	 * @return  string  The panel HTML.
	 */
	public function startEditFieldsetPanel($fieldset, $fieldsets)
	{
		$f_tab = $this->get('f_tab');

		// Get Panel Type
		if (!empty($fieldset->tab))
		{
			$panel_type     = 'tab';
			$panel_name     = $fieldset->tab;
			$start_function = 'startTabs';
			$add_function   = 'addPanel';
		}
		elseif (!empty($fieldset->slide))
		{
			$panel_type     = 'slide';
			$panel_name     = $fieldset->slide;
			$start_function = 'startSlider';
			$add_function   = 'addSlide';
		}
		else
		{
			return true;
		}

		// Set Panel
		if ($panel_name)
		{
			if ($fieldset->$panel_type != $f_tab)
			{
				echo AKHelper::_('panel.' . $start_function, $panel_name . ucfirst($panel_type), array('active' => $fieldset->name));
				$this->tab_count = count(AKHelper::_('array.query', $fieldsets, array($panel_type => $panel_name)));
				$this->tab_num   = 1;
			}

			echo AKHelper::_(
				'panel.' . $add_function, $fieldset->$panel_type . ucfirst($panel_type),
				$fieldset->label ? JText::_($fieldset->label) : JText::_(strtoupper($this->option) . '_' . 'EDIT_FIELDSET_' . $fieldset->name),
				$fieldset->name
			);
		}
	}

	/**
	 * A method to render end of fieldset tabs or slider.
	 *
	 * @param   object $fieldset
	 *
	 * @return  string  The panel HTML.
	 */
	public function endEditFieldsetPanel($fieldset)
	{
		// Get Panel Type
		if (!empty($fieldset->tab))
		{
			$panel_type        = 'tab';
			$panel_name        = $fieldset->tab;
			$endpanel_function = 'endPanel';
			$end_function      = 'endTabs';
		}
		elseif (!empty($fieldset->slide))
		{
			$panel_type        = 'slide';
			$panel_name        = $fieldset->slide;
			$endpanel_function = 'endSlide';
			$end_function      = 'endSlider';
		}
		else
		{
			return true;
		}

		// Tabs & Slides end
		if (!empty($fieldset->$panel_type))
		{
			echo AKHelper::_('panel.' . $endpanel_function, $panel_name . ucfirst($panel_type), $fieldset->name);
		}

		if ($this->tab_num == $this->tab_count)
		{
			echo AKHelper::_('panel.' . $end_function);
		}

		$this->f_tab = !empty($panel_name) ? $fieldset->$panel_type : null;
		$this->tab_num++;
	}
}
