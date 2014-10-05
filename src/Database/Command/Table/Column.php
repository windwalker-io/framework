<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database\Command\Table;

/**
 * The Column class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Column
{
	/**
	 * @var boolean
	 */
	const SIGNED = true;

	/**
	 * @var boolean
	 */
	const UNSIGNED = false;

	/**
	 * @var boolean
	 */
	const ALLOW_NULL = true;

	/**
	 * @var boolean
	 */
	const NOT_NULL = false;

	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = null;

	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type;

	/**
	 * Property length.
	 *
	 * @var integer
	 */
	protected $length;

	/**
	 * Property signed.
	 *
	 * @var  bool
	 */
	protected $signed;

	/**
	 * Property nallowNull.
	 *
	 * @var  bool
	 */
	protected $allowNull;

	/**
	 * Property default.
	 *
	 * @var  string
	 */
	protected $default;

	/**
	 * Property position.
	 *
	 * @var  string
	 */
	protected $position;

	/**
	 * Property comment.
	 *
	 * @var  string
	 */
	protected $comment;

	/**
	 * Property autoIncrement.
	 *
	 * @var  boolean
	 */
	protected $autoIncrement = false;

	/**
	 * Class init.
	 *
	 * @param string $name
	 * @param string $type
	 * @param bool   $unsigned
	 * @param bool   $notNull
	 * @param string $default
	 * @param null   $position
	 * @param string $comment
	 */
	public function __construct($name = null, $type = 'text', $unsigned = false, $notNull = false, $default = '', $position = null, $comment = '')
	{
		$this->name = $name;
		$this->type = $type;
		$this->unsigned = $unsigned;
		$this->notNull = $notNull;
		$this->default = $default;
		$this->position = $position;
		$this->comment = $comment;
	}

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
	 * @return  string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Method to set property type
	 *
	 * @param   string $type
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Method to get property Position
	 *
	 * @return  string
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * Method to set property position
	 *
	 * @param   string $position
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPosition($position)
	{
		$this->position = $position;

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

	/**
	 * Method to get property Signed
	 *
	 * @return  boolean
	 */
	public function getSigned()
	{
		return $this->signed;
	}

	/**
	 * Method to set property signed
	 *
	 * @param   boolean $signed
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setSigned($signed)
	{
		$this->signed = $signed;

		return $this;
	}

	/**
	 * Method to get property AllowNull
	 *
	 * @return  boolean
	 */
	public function getAllowNull()
	{
		return $this->allowNull;
	}

	/**
	 * Method to set property allowNull
	 *
	 * @param   boolean $allowNull
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setAllowNull($allowNull)
	{
		$this->allowNull = $allowNull;

		return $this;
	}

	/**
	 * Method to get property Default
	 *
	 * @return  string
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * Method to set property default
	 *
	 * @param   string $default
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDefault($default)
	{
		$this->default = $default;

		return $this;
	}

	/**
	 * Method to get property AutoIncrement
	 *
	 * @return  boolean
	 */
	public function getAutoIncrement()
	{
		return $this->autoIncrement;
	}

	/**
	 * Method to set property autoIncrement
	 *
	 * @param   boolean $autoIncrement
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setAutoIncrement($autoIncrement)
	{
		$this->autoIncrement = $autoIncrement;

		return $this;
	}

	/**
	 * Method to get property Length
	 *
	 * @return  int
	 */
	public function getLength()
	{
		return $this->length;
	}

	/**
	 * Method to set property length
	 *
	 * @param   int $length
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setLength($length)
	{
		$this->length = $length;

		return $this;
	}
}
