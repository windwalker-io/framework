<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Test\Stub;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Response\AbstractContentTypeResponse;
use Windwalker\Http\Stream\Stream;

/**
 * The StubContentTypeResponse class.
 *
 * @since  {DEPLOY_VERSION}
 */
class StubContentTypeResponse extends AbstractContentTypeResponse
{
	/**
	 * handleBody
	 *
	 * @param   string $body
	 *
	 * @return  StreamInterface
	 */
	protected function handleBody($body)
	{
		$stream = new Stream('php://memory', 'rw+');
		$stream->write($body);
		$stream->rewind();

		return $stream;
	}
}
