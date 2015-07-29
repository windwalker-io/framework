<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO;

/**
 * Windwalker Input JSON Class
 *
 * This class decodes a JSON string from the raw request data and makes it available via
 * the standard Input interface.
 *
 * @since  2.0
 */
class JsonInput extends Input
{
	/**
	 * The raw JSON string from the request.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $raw;

	/**
	 * Prepare source.
	 *
	 * @param   array  $source  Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 *
	 * @return  void
	 */
	protected function prepareSource($source = null)
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
	 * @since   2.0
	 */
	public function getRaw()
	{
		return $this->raw;
	}
}
