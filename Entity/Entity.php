<?php
/**
 * Part of datamapper project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper\Entity;

use Windwalker\Data\Data;

/**
 * Class Entity
 *
 * @since 1.0
 */
class Entity extends Data
{
	/**
	 * Constructor.
	 *
	 * @param array $fields
	 * @param mixed $data
	 */
	public function __construct($fields = null, $data = null)
	{
		if ($fields)
		{
			$this->addFields($fields);
		}

		parent::__construct($data);
	}

	/**
	 * addFields
	 *
	 * @param array $array
	 *
	 * @return  $this
	 */
	public function addFields($array)
	{
		foreach ($array as $field)
		{
			$this->addField($field);
		}

		return $this;
	}

	/**
	 * addField
	 *
	 * @param string $field
	 *
	 * @return  $this
	 */
	public function addField($field)
	{
		$this->$field = null;

		return $this;
	}

	/**
	 * removeField
	 *
	 * @param string $field
	 *
	 * @return  $this
	 */
	public function removeField($field)
	{
		unset($this->$field);

		return $this;
	}

	/**
	 * set
	 *
	 * @param string $field
	 * @param mixed  $value
	 *
	 * @return  $this|Data
	 */
	public function set($field, $value = null)
	{
		if (!property_exists($this, $field))
		{
			return $this;
		}

		return parent::set($field, $value);
	}

	/**
	 * set
	 *
	 * @param string $field
	 * @param mixed  $value
	 *
	 * @return  Data
	 */
	public function __set($field, $value = null)
	{
		return $this->$field = $value;
	}
}
