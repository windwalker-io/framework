<?php
/**
 * Part of joomla321 project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Helper;

use Windwalker\Data\Data;

/**
 * Class GridHelper
 *
 * @since 1.0
 */
class GridHelper
{
	/**
	 * Property view.
	 *
	 * @var object
	 */
	protected $view;

	/**
	 * Property config.
	 *
	 * @var \JRegistry
	 */
	protected $config = array();

	/**
	 * Property fields.
	 *
	 * @var array
	 */
	protected $fields = array(
		'pk'               => 'id',
		'title'            => 'title',
		'alias'            => 'alias',
		'checked_out'      => 'checked_out',
		'published'        => 'published',
		'author'           => 'created_by',
		'author_name'      => 'user_name',
		'checked_out_time' => 'checked_out_time',
	);

	/**
	 * Property state.
	 *
	 * @var \JRegistry
	 */
	protected $state;

	/**
	 * Property current.
	 *
	 * @var
	 */
	protected $current;

	/**
	 * Property row.
	 *
	 * @var
	 */
	protected $row;

	/**
	 * Constructor.
	 *
	 * @param object $view
	 * @param array  $config
	 */
	public function __construct($view, $config = array())
	{
		$this->view   = $view;
		$this->config = $config = new \JRegistry($config);
		$this->state  = $state = $view->state;

		// Merge fields
		$fields = $config->get('field');

		$fields = array_merge($this->fields, (array) $fields);

		$this->config->set('field', (object) $fields);

		// Access context
		$this->context = $this->config->get('option') . '.' . $this->config->get('view_item');

		// Ordering
		$listOrder = $state->get('list.ordering');
		$orderCol  = $state->get('list.orderCol', $config->get('orderCol'));

		$state->set('list.saveorder', ($listOrder == $orderCol));
	}

	/**
	 * registerTableSort
	 *
	 * @param null $task
	 *
	 * @return bool
	 */
	public function registerTableSort($task = null)
	{
		if (!$this->state->get('list.saveorder', false))
		{
			return false;
		}

		$option    = $this->config->get('option');
		$task      = $task ? : $this->config->get('view_list') . '.state.reorder';
		$listDirn  = $this->state->get('list.direction');

		$saveOrderingUrl = 'index.php?option=' . $option . '&task=' . $task . '&tmpl=component';

		\JHtml::_('sortablelist.sortable', 'sakuraList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);

		return true;
	}

	/**
	 * sortTitle
	 *
	 * @param string $label
	 * @param string $field
	 * @param string $task
	 * @param string $new_direction
	 * @param string $tip
	 * @param string $icon
	 *
	 * @return mixed
	 */
	public function sortTitle($label, $field, $task = null, $new_direction = 'asc', $tip = '', $icon = null)
	{
		$listOrder = $this->state->get('list.ordering');
		$listDirn  = $this->state->get('list.direction');
		$formName  = $this->config->get('form_name', 'adminForm');

		return \JHtml::_('searchtools.sort', $label, $field, $listDirn, $listOrder, $task, $new_direction, $tip, $icon, $formName);
	}

	/**
	 * orderTitle
	 *
	 * @return string
	 */
	public function orderTitle()
	{
		$orderCol = $this->state->get('list.orderCol', $this->config->get('orderCol'));

		return $this->sortTitle('', $orderCol, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2');
	}

	/**
	 * setItem
	 *
	 * @param mixed $item
	 * @param int   $i
	 *
	 * @return bool
	 */
	public function setItem($item, $i)
	{
		if (!($item instanceof \JData))
		{
			$item = new Data($item);
		}

		$this->row = (int) $i;

		$pkName       = $this->config->get('field.pk', 'id');
		$checkoutName = $this->config->get('field.checked_out', 'checked_out');
		$authorName   = $this->config->get('field.author', 'created_by');

		$user   = \JFactory::getUser();
		$userId = $user->get('id');

		$this->current = $item;

		// Don't check access.
		if (!$this->config->get('ignore_access', false))
		{
			$this->state->set('access.canEdit',    true);
			$this->state->set('access.canCheckin', true);
			$this->state->set('access.canChange',  true);
			$this->state->set('access.canEditOwn', true);

			return true;
		}

		$canEdit    = $user->authorise('core.edit', $this->context . '.' . $item->$pkName);
		$canCheckin = $user->authorise('core.edit.state', $this->context . '.' . $item->$pkName) || $item->$checkoutName == $userId || $item->$checkoutName == 0;
		$canChange  = $user->authorise('core.edit.state', $this->context . '.' . $item->$pkName) && $canCheckin;
		$canEditOwn = $user->authorise('core.edit.own', $this->context . '.' . $item->$pkName) && $item->$authorName == $userId;

		$this->state->set('access.canEdit', $canEdit);
		$this->state->set('access.canCheckin', $canCheckin);
		$this->state->set('access.canChange', $canChange);
		$this->state->set('access.canEditOwn', $canEditOwn);
	}

	/**
	 * dragSort
	 *
	 * @return string
	 */
	public function dragSort()
	{
		$iconClass  = '';
		$input      = '';
		$item       = $this->current;
		$orderField = $this->config->get('field.ordering', 'ordering');
		$canChange  = $this->state->get('access.canChange', true);
		$saveOrder  = $this->state->get('list.saveorder', true);

		if (!$canChange)
		{
			$iconClass = ' inactive';
		}
		elseif (!$saveOrder)
		{
			$iconClass = ' inactive tip-top hasTooltip" title="' . \JHtml::tooltipText('JORDERINGDISABLED');
		}

		if ($canChange && $saveOrder)
		{
			$input = '<input type="text" style="display:none" name="order[]" size="5" value="' . $item->$orderField . '" class="width-20 text-area-order " />';
		}

		$html = <<<HTML
		<span class="sortable-handler{$iconClass}">
			<i class="icon-menu"></i>
		</span>
		{$input}
		<span class="label">
			{$item->$orderField}
		</span>
HTML;

		return $html;
	}

	/**
	 * editTitle
	 *
	 * @param array  $append
	 * @param string $task
	 * @param array  $attribs
	 *
	 * @return string
	 */
	public function editTitle($append = array(), $task = null, $attribs = null)
	{
		$canEdit    = $this->state->get('access.canEdit', true);
		$canEditOwn = $this->state->get('access.canEditOwn', true);

		$item       = $this->current;
		$pkName     = $this->config->get('field.pk');
		$titleField = $this->config->get('field.title');

		$query = array(
			'option' => $this->config->get('option'),
			'task'   => $task ? : $this->config->get('view_item') . '.edit',
			$pkName  => $this->current->$pkName
		);

		$query = array_merge($query, $append);

		$uri = new \JUri;

		$uri->setQuery($query);

		if ($canEdit || $canEditOwn)
		{
			return \JHtml::link($uri, $this->escape($item->$titleField), $attribs);
		}
		else
		{
			return $this->escape($item->$titleField);
		}
	}

	/**
	 * published
	 *
	 * @param string $taskPrefix
	 *
	 * @return mixed
	 */
	public function published($taskPrefix = null)
	{
		$item       = $this->current;
		$canChange  = $this->state->get('access.canChange', true);
		$taskPrefix = $taskPrefix ? : $this->config->get('view_list') . '.state.';
		$field      = $this->config->get('field.published', 'published');

		return \JHtml::_('jgrid.published', $item->$field, $this->row, $taskPrefix, $canChange, 'cb', $item->publish_up, $item->publish_down);
	}

	/**
	 * checkoutButton
	 *
	 * @param string $taskPrefix
	 *
	 * @return string
	 */
	public function checkoutButton($taskPrefix = null)
	{
		$item  = $this->current;
		$field = $this->config->get('field.checked_out', 'checked_out');

		$authorNameField = $this->config->get('field.author_name');
		$chkTimeField    = $this->config->get('field.checked_out_time');
		$canCheckin      = $this->state->get('access.canCheckin', true);
		$taskPrefix      = $taskPrefix ? : $this->config->get('view_list') . '.check.';

		if (!$item->$field)
		{
			return '';
		}

		return \JHtml::_(
			'jgrid.checkedout',
			$this->row,
			$item->$authorNameField,
			$item->$chkTimeField,
			$taskPrefix,
			$canCheckin
		);
	}

	/**
	 * Show a boolean icon.
	 *
	 * @param   mixed  $value   A variable has value or not.
	 * @param   string $task    Click to call a component task. Not available yet.
	 * @param   array  $options Some options.
	 *
	 * @return  string  A boolean icon HTML string.
	 */
	public function booleanIcon($value, $task = '', $options = array())
	{
		$class = $value ? 'icon-publish' : 'icon-unpublish';

		return "<i class=\"{$class}\"></i>";
	}

	/**
	 * can
	 *
	 * @param string $action
	 *
	 * @return boolean
	 */
	public function can($action)
	{
		$action = 'can' . ucfirst($action);

		return $this->state->get('access.' . $action, true);
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string $output The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @see     \JView::escape()
	 * @since   12.1
	 */
	public function escape($output)
	{
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}
}
