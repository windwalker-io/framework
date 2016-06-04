<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

use Windwalker\Http\Response\Response;
use Windwalker\Http\Stream\Stream;

/**
 * The EmptyResponse class.
 *
 * Always return empty data and is only readable. THe headers will still send.
 *
 * @since  {DEPLOY_VERSION}
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
		$body = new Stream('php://temp', Stream::MODE_READ_ONLY_FROM_BEGIN);

		parent::__construct($body, $status, $headers);
	}
}
