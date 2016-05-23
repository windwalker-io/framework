<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

use Windwalker\Http\Response;

/**
 * The AbstractContentTypeResponse class.
 *
 * @since  {DEPLOY_VERSION}
 */
class AbstractContentTypeResponse extends Response
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'application/text';

	/**
	 * Inject the provided Content-Type, if none is already present.
	 *
	 * @param   string  $contentType  The content type.
	 *
	 * @return  static
	 */
	public function withContentType($contentType)
	{
		$contentType = $this->normalizeContentType($contentType);

		$this->type = $contentType;

		return $this->withHeader('Content-Type', $contentType);
	}

	/**
	 * injectContentType
	 *
	 * @param   string  $contentType
	 *
	 * @return  void
	 */
	protected function injectContentType($contentType)
	{
		$name = $this->getHeaderName('Content-Type');

		if (isset($this->headers[$name]))
		{
			return;
		}

		$this->headers[$name] = array($contentType);
	}

	/**
	 * normalizeContentType
	 *
	 * @param   string  $contentType
	 *
	 * @return  string
	 */
	protected function normalizeContentType($contentType)
	{
		return strtolower($contentType);
	}
}
