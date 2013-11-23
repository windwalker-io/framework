<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Form
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('libraries.form.form');

/**
 * A Form constructor class extends from JForm to enhance some function.
 *
 * @package     Windwalker.Framework
 * @subpackage  Form
 */
class AKForm extends JForm
{
	/**
	 * Fields group.
	 *
	 * @var array
	 */
	public $fields;

	/**
	 * Get data and handle them for prepare save.
	 *
	 * @param   string $profile A fields group name.
	 * @param   array  $data    The data for save.
	 *
	 * @return  array   Handled tree data.
	 */
	public function getDataForSave($profile, $data = null)
	{
		if ($data)
		{
			$this->bind($data);
		}

		$fields = $this->getGroup($profile);
		$data2  = array();

		foreach ($fields as $field):
			$data2[$field->fieldname] = $field->value;
		endforeach;

		return $data2;
	}

	/**
	 * Get data and set every fields' value to format for show.
	 *
	 * @param   string $profile A fields group name.
	 * @param   array  $data    The data for show.
	 *
	 * @return  array   Handled tree data.
	 */
	public function getDataForShow($profile, $data = null)
	{
		if ($data)
		{
			$this->bind($data);
		}

		$fields = $this->getGroup($profile);
		$data2  = array();

		foreach ($fields as $field):
			if (method_exists($field, 'showData'))
			{
				$data2[$field->fieldname] = $field->showData();
			}
			else
			{
				$data2[$field->fieldname] = $field->value;
			}
		endforeach;

		return $data2;
	}
}
