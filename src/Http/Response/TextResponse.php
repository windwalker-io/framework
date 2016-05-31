<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Stream\Stream;
use Windwalker\Http\Stream\StringStream;

/**
 * The TextResponse class.
 *
 * @since  {DEPLOY_VERSION}
 */
class TextResponse extends AbstractContentTypeResponse
{
	/**
	 * Handle stream message body.
	 *
	 * @param   string $text
	 *
	 * @return  StringStream
	 */
	protected function handleBody($text)
	{
		if (is_string($text))
		{
			$stream = new Stream('php://temp', 'wb+');
			$stream->write($text);
			$stream->rewind();

			$text = $stream;
		}

		if (!$text instanceof StreamInterface)
		{
			throw new \InvalidArgumentException(sprintf(
				'Invalid body content type %s, please provide string or StreamInterface',
				gettype($text)
			));
		}

		return $text;
	}
}
