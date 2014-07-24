<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO;

use Joomla\Filter;
use Joomla\Filter\InputFilter;

/**
 * Windwalker Input JSON Class
 *
 * This class decodes a JSON string from the raw request data and makes it available via
 * the standard Input interface.
 *
 * @since  1.0
 */
class JsonInput extends Input
{
	/**
	 * The raw JSON string from the request.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $raw;

	/**
	 * Constructor.
	 *
	 * @param   array       $source Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   InputFilter $filter The input filter object.
	 *
	 * @since   1.0
	 */
	public function __construct(array $source = null, InputFilter $filter = null)
	{
		if (is_null($source))
		{
			$this->raw = file_get_contents('php://input');

			// This is a workaround for where php://input has already been read.
			// See note under php://input on http://php.net/manual/en/wrappers.php.php
			if (empty($this->raw) && isset($GLOBALS['HTTP_RAW_POST_DATA']))
			{
				$this->raw = $GLOBALS['HTTP_RAW_POST_DATA'];
			}

			$this->data = json_decode($this->raw, true);

			if (!is_array($this->data))
			{
				$this->data = array();
			}
		}
		else
		{
			$this->data = $source;
		}
	}

	/**
	 * Gets the raw JSON string from the request.
	 *
	 * @return  string  The raw JSON string from the request.
	 *
	 * @since   1.0
	 */
	public function getRaw()
	{
		return $this->raw;
	}
}
