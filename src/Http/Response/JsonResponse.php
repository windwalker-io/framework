<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

/**
 * The HtmlResponse class.
 *
 * @since  3.0
 */
class JsonResponse extends TextResponse
{
	/**
	 * Content type.
	 *
	 * @var  string
	 */
	protected $type = 'application/json';

	/**
	 * Constructor.
	 *
	 * @param  string $json    The JSON body data.
	 * @param  int    $status  The status code.
	 * @param  array  $headers The custom headers.
	 * @param  int    $options Json encode options.
	 */
	public function __construct($json = '', $status = 200, array $headers = array(), $options = 0)
	{
		parent::__construct(
			$this->encode($json, $options),
			$status,
			$headers
		);
	}

	/**
	 * Encode json.
	 *
	 * @param   mixed $data     The dat to convert.
	 * @param   int   $options  The json_encode() options flag.
	 *
	 * @return  string  Encoded json.
	 */
	protected function encode($data, $options = 0)
	{
		// Check is already json string.
		if (is_string($data) && strlen($data) >= 1)
		{
			$firstChar = $data[0];

			if (in_array($firstChar, array('[', '{', '"')))
			{
				return $data;
			}
		}

		// Clear json_last_error()
		json_encode(null);

		$json = json_encode($data, $options);

		if (json_last_error() !== JSON_ERROR_NONE)
		{
			throw new \UnexpectedValueException(sprintf('JSON encode failure: %s', json_last_error_msg()));
		}

		return $json;
	}

	/**
	 * withContent
	 *
	 * @param   mixed $content
	 *
	 * @return  static
	 * @throws \InvalidArgumentException
	 */
	public function withContent($content)
	{
		return parent::withContent($this->encode($content));
	}
}
