<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

use Psr\Http\Message\UriInterface;
use Windwalker\Http\Stream\Stream;

/**
 * The RedirectResponse class.
 *
 * @since  3.0
 */
class RedirectResponse extends Response
{
	/**
	 * Constructor.
	 *
	 * @param  string|UriInterface  $uri      The redirect uri.
	 * @param  int                  $status   The status code.
	 * @param  array                $headers  The custom headers.
	 */
	public function __construct($uri, $status = 303, array $headers = array())
	{
		if ($uri instanceof UriInterface || $uri instanceof \Windwalker\Uri\UriInterface)
		{
			$uri = (string) $uri;
		}

		if (!is_string($uri))
		{
			throw new \InvalidArgumentException(sprintf(
				'Invalid URI type, string or UriInterface required, %s provided.',
				gettype($uri)
			));
		}

		$headers['location'] = array($uri);

		parent::__construct(new Stream('php://temp', 'r'), $status, $headers);
	}
}
