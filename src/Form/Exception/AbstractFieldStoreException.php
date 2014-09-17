<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Exception;

use Windwalker\Form\Field\FieldInterface;

/**
 * The AbstractFieldStoreException class.
 * 
 * @since  {DEPLOY_VERSION}
 *
 * @deprecated  Do not use exception to handle validate fail anymore.
 */
class AbstractFieldStoreException extends ValidateFailException
{
	/**
	 * Property field.
	 *
	 * @var  FieldInterface
	 */
	protected $field = null;

	/**
	 * Constructor.
	 *
	 * @param FieldInterface $field
	 * @param string         $message
	 * @param int            $code
	 * @param \Exception     $previous
	 */
	public function __construct($field, $message = "", $code = 0, \Exception $previous = null)
	{
		$this->field = $field;

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Method to get property Field
	 *
	 * @return  \Windwalker\Form\Field\FieldInterface
	 */
	public function getField()
	{
		return $this->field;
	}

	/**
	 * Method to set property field
	 *
	 * @param   \Windwalker\Form\Field\FieldInterface $field
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setField($field)
	{
		$this->field = $field;

		return $this;
	}
}

