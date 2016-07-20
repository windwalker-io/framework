<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Test\Stub;

use Windwalker\Http\Output\Output;
use Windwalker\Http\Response\Response;

/**
 * The MockOutput class.
 *
 * @since  3.0
 */
class StubOutput extends Output
{
	/**
	 * Property message.
	 *
	 * @var  Response
	 */
	public $message;

	/**
	 * Property status.
	 *
	 * @var  integer
	 */
	public $status;

	/**
	 * Property others.
	 *
	 * @var  array
	 */
	public $others = array();

	/**
	 * MockOutput constructor.
	 */
	public function __construct()
	{
		$this->message = new Response;
	}

	/**
	 * header
	 *
	 * @param string  $string
	 * @param bool    $replace
	 * @param integer $code
	 *
	 * @return  static
	 */
	public function header($string, $replace = true, $code = null)
	{
		if (strpos($string, ':') !== false)
		{
			list($header, $value) = explode(': ', $string, 2);

			if ($replace)
			{
				$this->message = $this->message->withHeader($header, $value);
			}
			else
			{
				$this->message = $this->message->withAddedHeader($header, $value);
			}
		}
		elseif (strpos($string, 'HTTP') === 0)
		{
			$this->status = $string;
		}
		else
		{
			$this->others[] = $string;
		}
		
		return $this;
	}
}
