<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Response;
use Windwalker\Http\Stream\StringStream;

/**
 * The HtmlResponse class.
 *
 * @since  {DEPLOY_VERSION}
 */
class HtmlResponse extends AbstractContentTypeResponse
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'text/html';

	/**
	 * HtmlResponse constructor.
	 */
	public function __construct($html, $status = 200, array $headers)
	{
		parent::__construct(
			$this->handleBody($html),
			$status,
			$headers
		);
		
		$this->injectContentType($this->type);
	}

	/**
	 * Handle stream message body.
	 *
	 * @param   string  $html
	 *
	 * @return  StringStream
	 */
	protected function handleBody($html)
	{
		if (is_string($html))
		{
			$html = new StringStream($html, 'wb+');
			$html->rewind();
		}

		if (!$html instanceof StreamInterface)
		{
			throw new \InvalidArgumentException(sprintf(
				'Invalid HTML body content type %s, please provide string or StreamInterface',
				gettype($html)
			));
		}

		return $html;
	}
}
