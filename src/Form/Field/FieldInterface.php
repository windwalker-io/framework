<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field;

/**
 * The FieldIntrface class.
 * 
 * @since  {DEPLOY_VERSION}
 */
interface FieldInterface
{
	/**
	 * getInput
	 *
	 * @return  string
	 */
	public function getInput();

	/**
	 * validate
	 *
	 * @return  boolean
	 */
	public function validate();

	/**
	 * renderView
	 *
	 * @return  string
	 */
	public function renderView();

	/**
	 * Method to get property Name
	 *
	 * @param bool $withGroup
	 *
	 * @return  string
	 */
	public function getName($withGroup = false);

	/**
	 * Method to get property Fieldset
	 *
	 * @return  null
	 */
	public function getFieldset();

	/**
	 * Method to get property Group
	 *
	 * @return  null
	 */
	public function getGroup();

	/**
	 * Method to get property Value
	 *
	 * @return  null
	 */
	public function getValue();

	/**
	 * Method to get property Control
	 *
	 * @return  string
	 */
	public function getControl();

	/**
	 * Method to set property control
	 *
	 * @param   string $control
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setControl($control);

	/**
	 * getAttribute
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	public function getAttribute($name, $default = null);
	/**
	 * getAttribute
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return  mixed
	 */
	public function setAttribute($name, $value);
}
 