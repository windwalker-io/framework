<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Schema;

/**
 * The Column class.
 * 
 * @since  2.0
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
	 * Property allowNull.
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
	 * Property primary.
	 *
	 * @var boolean
	 */
	protected $primary;

	/**
	 * Class init.
	 *
	 * @param string $name
	 * @param string $type
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $comment
	 * @param array  $options
	 */
	public function __construct($name = null, $type = 'text', $signed = false, $allowNull = false, $default = null, $comment = '', $options = [])
	{
		$this->name = $name;
		$this->type = $type;
		$this->signed = $signed;
		$this->allowNull = $allowNull;
		$this->default = $default;
		$this->comment = $comment;

		$this->setOptions($options);
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
	public function name($name)
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
	public function type($type)
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
	public function position($position)
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
	public function comment($comment)
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
	public function signed($signed = true)
	{
		$this->signed = $signed;

		return $this;
	}

	/**
	 * unsigned
	 *
	 * @return  static
	 */
	public function unsigned()
	{
		$this->signed(false);

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
	public function allowNull($allowNull = true)
	{
		$this->allowNull = $allowNull;

		return $this;
	}

	/**
	 * notNull
	 *
	 * @return  static
	 */
	public function notNull()
	{
		$this->allowNull(false);

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
	public function defaultValue($default)
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
	public function autoIncrement($autoIncrement = true)
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
	public function length($length)
	{
		$this->length = $length;

		return $this;
	}

	/**
	 * setOptions
	 *
	 * @param array $options
	 *
	 * @return  static
	 */
	public function setOptions(array $options)
	{
		$defaultOptions = [
			'primary' => false,
			'auto_increment' => false,
			'position' => null,
			'length' => null
		];

		$options = array_merge($defaultOptions, $options);

		if ($options['primary'])
		{
			$options['auto_increment'] = true;

			$this->signed = false;
			$this->allowNull = false;
		}

		$this->autoIncrement = $options['auto_increment'];
		$this->position = $options['position'];
		$this->length = $options['length'];
		$this->primary = $options['primary'];

		return $this;
	}

	/**
	 * Method to get property Primary
	 *
	 * @return  boolean
	 */
	public function isPrimary()
	{
		return $this->primary;
	}

	/**
	 * Method to set property primary
	 *
	 * @param   boolean $primary
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function primary($primary)
	{
		$primary = (bool) $primary;

		$this->primary = $primary;

		if ($primary)
		{
			$this->signed = false;
			$this->allowNull = false;
			$this->autoIncrement = true;
		}

		return $this;
	}
}
