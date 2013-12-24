<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model;

defined('JPATH_PLATFORM') or die;

/**
 * Prototype item model.
 *
 * @package     Joomla.Legacy
 * @subpackage  Model
 * @since       12.2
 */
abstract class AdminModel extends FormModel
{
	/**
	 * An item.
	 *
	 * @var    array
	 */
	protected $item = null;

	/**
	 * Model context string.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $context = 'group.type';

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $textPrefix = null;

	/**
	 * The event to trigger after deleting the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventAfterDelete = null;

	/**
	 * The event to trigger after saving the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventAfterSave = null;

	/**
	 * The event to trigger before deleting the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventBeforeDelete = null;

	/**
	 * The event to trigger before saving the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventBeforeSave = null;

	/**
	 * The event to trigger after changing the published state of the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventChangeState = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModel
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->eventAfterDelete  = \JArrayHelper::getValue($config, 'event_after_delete', 'onContentAfterDelete');

		$this->eventBeforeDelete = \JArrayHelper::getValue($config, 'event_before_delete', 'onContentBeforeDelete');

		$this->eventAfterSave    = \JArrayHelper::getValue($config, 'event_after_save', 'onContentAfterSave');

		$this->eventBeforeSave   = \JArrayHelper::getValue($config, 'event_before_save', 'onContentBeforeSave');

		$this->eventChangeState  = \JArrayHelper::getValue($config, 'event_change_state', 'onContentChangeState');

		// @TODO: Check is needed or not.
		$this->textPrefix = strtoupper(\JArrayHelper::getValue($config, 'text_prefix', $this->option));
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = \JFactory::getApplication()->input->getInt($key);
		$this->state->set($this->getName() . '.id', $pk);

		// Load the parameters.
		$value = \JComponentHelper::getParams($this->option);
		$this->state->set('params', $value);
	}
}
