<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Form;

use Windwalker\Dom\HtmlElement;

/**
 * The FormWrapper class.
 * 
 * @since  2.1
 */
class FormWrapper extends HtmlElement
{
	const METHOD_GET  = 'get';
	const METHOD_POST = 'post';

	const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
	const ENCTYPE_FORM_DATA  = 'multipart/form-data';
	const ENCTYPE_TEXT_PLAIN = 'text/plain';

	/**
	 * Property tokenHandler.
	 *
	 * @var  string
	 */
	protected static $tokenHandler;

	/**
	 * Constructor
	 *
	 * @param mixed  $content Element content.
	 * @param array  $attribs Element attributes.
	 */
	public function __construct($content = null, $attribs = array())
	{
		parent::__construct('form', $content, $attribs);
	}

	/**
	 * create
	 *
	 * @param mixed  $content
	 * @param array  $attribs
	 *
	 * @return  static
	 */
	public static function create($content = null, $attribs = array())
	{
		return new static($content, $attribs);
	}

	/**
	 * start
	 *
	 * @param string  $name
	 * @param string  $method
	 * @param string  $action
	 * @param string  $enctype
	 * @param array   $attribs
	 *
	 * @return  string
	 */
	public static function start($name = null, $method = null, $action = null, $enctype = null, $attribs = array())
	{
		$form = static::create()
			->name($name)
			->setAttribute('id', $name)
			->method($method)
			->action($action)
			->enctype($enctype);

		foreach ($attribs as $key => $value)
		{
			$form->setAttribute($key, $value);
		}

		return $form->renderStart();
	}

	/**
	 * end
	 *
	 * @return  string
	 */
	public static function end()
	{
		return static::getToken() . '</form>';
	}

	/**
	 * renderStart
	 *
	 * @return  string
	 */
	public function renderStart()
	{
		$html = $this->toString(true);

		return substr($html, 0, -7);
	}

	/**
	 * renderEnd
	 *
	 * @return  string
	 */
	public function renderEnd()
	{
		return static::end();
	}

	/**
	 * getToken
	 *
	 * @return  string
	 */
	public static function getToken()
	{
		if (static::$tokenHandler)
		{
			return call_user_func(static::$tokenHandler);
		}

		return '';
	}

	/**
	 * acceptCharset
	 *
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function acceptCharset($value)
	{
		$this->attribs['accept-charset'] = $value;

		return $this;
	}

	/**
	 * action
	 *
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function action($value)
	{
		$this->attribs['action'] = $value;

		return $this;
	}

	/**
	 * autocomplete
	 *
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function autocomplete($value)
	{
		$this->attribs['autocomplete'] = $value;

		return $this;
	}

	/**
	 * enctype
	 *
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function enctype($value)
	{
		$this->attribs['enctype'] = $value;

		return $this;
	}

	/**
	 * method
	 *
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function method($value)
	{
		$this->attribs['method'] = $value;

		return $this;
	}

	/**
	 * name
	 *
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function name($value)
	{
		$this->attribs['name'] = $value;

		return $this;
	}

	/**
	 * novalidate
	 *
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function novalidate($value)
	{
		$value = (bool) $value;

		$this->attribs['novalidate'] = $value ? 'novalidate' : null;

		return $this;
	}

	/**
	 * target
	 *
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function target($value)
	{
		$this->attribs['target'] = $value;

		return $this;
	}

	/**
	 * accept
	 *
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function accept($value)
	{
		$this->attribs['accept'] = $value;

		return $this;
	}

	/**
	 * Method to get property TokenHandler
	 *
	 * @return  string
	 */
	public static function getTokenHandler()
	{
		return static::$tokenHandler;
	}

	/**
	 * Method to set property tokenHandler
	 *
	 * @param   string $tokenHandler
	 *
	 * @return  static  Return self to support chaining.
	 */
	public static function setTokenHandler($tokenHandler)
	{
		if ($tokenHandler !== null && !is_callable($tokenHandler))
		{
			throw new \InvalidArgumentException('Tokan handler not callable.');
		}

		static::$tokenHandler = $tokenHandler;
	}
}
