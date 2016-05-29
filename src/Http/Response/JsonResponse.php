<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Stream\Stream;
use Windwalker\Http\Stream\StringStream;

/**
 * The HtmlResponse class.
 *
 * @since  {DEPLOY_VERSION}
 */
class JsonResponse extends TextResponse
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'application/json';

	/**
	 * HtmlResponse constructor.
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
	 * encode
	 *
	 * @param mixed $data
	 * @param int   $options
	 *
	 * @return  string
	 */
	protected function encode($data, $options = 0)
	{
		//		if (is_null($options))
		//		{
		//			$options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES;
		//		}

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
}
