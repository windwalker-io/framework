<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Stream\Stream;

/**
 * The EmptyResponse class.
 *
 * Always return empty data and is only readable. THe headers will still send.
 *
 * @since  3.0
 */
class EmptyResponse extends Response
{
	/**
	 * Constructor.
	 *
	 * @param  int     $status   The status code.
	 * @param  array   $headers  The custom headers.
	 */
	public function __construct($status = 204, array $headers = array())
	{
		$body = new Stream('php://memory', Stream::MODE_READ_ONLY_FROM_BEGIN);

		parent::__construct($body, $status, $headers);
	}

	/**
	 * Gets the body of the message.
	 *
	 * @return StreamInterface Returns the body as a stream.
	 */
	public function getBody()
	{
		// Always return empty stream
		return new Stream('php://memory', Stream::MODE_READ_ONLY_FROM_BEGIN);
	}
}
