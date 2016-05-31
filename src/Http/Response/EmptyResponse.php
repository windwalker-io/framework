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
 * @since  {DEPLOY_VERSION}
 */
class EmptyResponse extends Response
{
	/**
	 * EmptyResponse constructor.
	 *
	 * @param int   $status
	 * @param array $headers
	 */
	public function __construct($status = 204, array $headers = array())
	{
		$body = new Stream('php://temp', 'r');

		parent::__construct($body, $status, $headers);
	}
}
