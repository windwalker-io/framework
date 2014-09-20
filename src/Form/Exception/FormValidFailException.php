<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Exception;

/**
 * The FormValidFailException class.
 * 
 * @since  {DEPLOY_VERSION}
 *
 * @deprecated  Do not use exception to handle validate fail anymore.
 */
class FormValidFailException extends ValidateFailException
{
	/**
	 * Property field.
	 *
	 * @var  AbstractFieldStoreException[]
	 */
	protected $fields = null;

	/**
	 * Constructor.
	 *
	 * @param AbstractFieldStoreException[] $fields
	 * @param string                        $message
	 * @param int                           $code
	 * @param \Exception                    $previous
	 *
	 * @deprecated  Do not use exception to handle validate fail anymore.
	 */
	public function __construct($fields, $message = "", $code = 0, \Exception $previous = null)
	{
		$this->setFields($fields);

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Method to get property Fields
	 *
	 * @return  AbstractFieldStoreException[]
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Method to set property fields
	 *
	 * @param   AbstractFieldStoreException[] $fields
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setFields($fields)
	{
		foreach ($fields as $field)
		{
			$this->addField($field);
		}

		return $this;
	}

	/**
	 * addField
	 *
	 * @param AbstractFieldStoreException $field
	 *
	 * @return  $this
	 */
	protected function addField(AbstractFieldStoreException $field)
	{
		$this->fields[] = $field;

		return $this;
	}
}

