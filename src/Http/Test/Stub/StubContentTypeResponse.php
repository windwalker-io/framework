<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Test\Stub;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Response\AbstractContentTypeResponse;
use Windwalker\Http\Stream\Stream;

/**
 * The StubContentTypeResponse class.
 *
 * @since  3.0
 */
class StubContentTypeResponse extends AbstractContentTypeResponse
{
	/**
	 * Handle body to stream object.
	 *
	 * @param   string  $body  The body data.
	 *
	 * @return  StreamInterface  Converted to stream object.
	 */
	protected function handleBody($body)
	{
		$stream = new Stream('php://memory', 'rw+');
		$stream->write($body);
		$stream->rewind();

		return $stream;
	}
}
