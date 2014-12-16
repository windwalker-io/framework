<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Schema;

/**
 * The Key class.
 * 
 * @since  2.0
 */
class Key
{
	/**
	 * @var string
	 */
	const TYPE_UNIQUE = 'unique';

	/**
	 * @var string
	 */
	const TYPE_INDEX = 'index';

	/**
	 * @var string
	 */
	const TYPE_PRIMARY = 'primary';

	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = null;

	/**
	 * Property type.
	 *
	 * @var  integer
	 */
	protected $type = null;

	/**
	 * Property columns.
	 *
	 * @var  array
	 */
	protected $columns = array();

	/**
	 * Property comment.
	 *
	 * @var  string
	 */
	protected $comment = '';

	/**
	 * Method to get property Name
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Method to set property name
	 *
	 * @param   string $name
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Method to get property Type
	 *
	 * @return  int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Method to set property type
	 *
	 * @param   int $type
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Method to get property Columns
	 *
	 * @return  array
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Method to set property columns
	 *
	 * @param   array $columns
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setColumns($columns)
	{
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Method to get property Comment
	 *
	 * @return  string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * Method to set property comment
	 *
	 * @param   string $comment
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;

		return $this;
	}
}
