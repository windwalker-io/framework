<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\IO;

/**
 * The FormInput class.
 *
 * @since  2.1.7
 */
class FormDataInput extends Input
{
	/**
	 * The raw JSON string from the request.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected static $raw;

	/**
	 * Prepare source.
	 *
	 * @param   array    $source     Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   boolean  $reference  If set to true, he source in first argument will be reference.
	 *
	 * @return  void
	 */
	public function prepareSource(&$source = null, $reference = false)
	{
		if (is_null($source))
		{
			$raw = static::loadRawFromRequest();

			if (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === 0)
			{
				static::parseFormData($raw, $this->data);
			}
			else
			{
				parse_str($raw, $this->data);
			}

			if (!is_array($this->data))
			{
				$this->data = array();
			}
		}
		else
		{
			if ($reference)
			{
				$this->data = &$source;
			}
			else
			{
				$this->data = $source;
			}
		}
	}

	/**
	 * Gets the raw HTTP data string from the request.
	 *
	 * @return  string  The raw HTP data string from the request.
	 *
	 * @since   2.0
	 */
	public static function getRawData()
	{
		return static::$raw;
	}

	/**
	 * setRawData
	 *
	 * @param   string  $data
	 *
	 * @return  string
	 */
	public static function setRawData($data)
	{
		static::$raw = $data;
	}

	/**
	 * loadRawFromRequest
	 *
	 * @return  string
	 */
	protected function loadRawFromRequest()
	{
		if (static::$raw)
		{
			return static::$raw;
		}

		static::$raw = file_get_contents('php://input');

		// This is a workaround for where php://input has already been read.
		// See note under php://input on http://php.net/manual/en/wrappers.php.php
		if (empty($this->raw) && isset($GLOBALS['HTTP_RAW_POST_DATA']))
		{
			static::$raw = $GLOBALS['HTTP_RAW_POST_DATA'];
		}

		return static::$raw;
	}

	/**
	 * parseFormData
	 *
	 * @param string $input
	 * @param array  &$data
	 *
	 * @link  http://stackoverflow.com/questions/5483851/manually-parse-raw-http-data-with-php/5488449#5488449
	 *
	 * @return  array
	 */
	public static function parseFormData($input, array &$data)
	{
		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);

		$boundary = $matches[1];

		// split content by boundary and get rid of last -- element
		$aBlocks = preg_split("/-+$boundary/", $input);

		array_pop($aBlocks);

		// loop data blocks
		foreach ($aBlocks as $id => $block)
		{
			if (empty($block))
			{
				continue;
			}

			// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

			// parse uploaded files
			if (strpos($block, 'application/octet-stream') !== false)
			{
				// match "name", then everything after "stream" (optional) except for prepending newlines
				preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
			}
			// parse all other fields
			else
			{
				// match "name" and optional value in between newline sequences
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?[\n|\r]$/s', $block, $matches);
			}

			if (isset($matches[1]) && isset($matches[2]))
			{
				$data[$matches[1]] = rtrim($matches[2], "\n\r");
			}
		}

		return $data;
	}
}
