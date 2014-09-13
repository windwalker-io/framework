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
	 * Property unsigned.
	 *
	 * @var  bool
	 */
	protected $unsigned;

	/**
	 * Property notNull.
	 *
	 * @var  bool
	 */
	protected $notNull;

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
	public function __construct($name, $type = 'text', $unsigned = false, $notNull = false, $default = '', $position = null, $comment = '')
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
}
