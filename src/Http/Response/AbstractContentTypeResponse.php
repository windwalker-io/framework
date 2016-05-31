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

/**
 * The AbstractContentTypeResponse class.
 *
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractContentTypeResponse extends Response
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'text/plain';

	/**
	 * HtmlResponse constructor.
	 */
	public function __construct($data = '', $status = 200, array $headers = array())
	{
		parent::__construct(
			$this->handleBody($data),
			$status,
			$this->addContentTypeToHeader($headers, $this->type . '; charset=utf-8')
		);
	}

	/**
	 * handleBody
	 *
	 * @param   string  $body
	 *
	 * @return  StreamInterface
	 */
	abstract protected function handleBody($body);

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

		$contentType = explode(';', $contentType, 2);

		$this->type = $contentType[0];

		$contentType[0] .= ';' . (isset($contentType[1]) ? $contentType[1] : ' charset=utf-8');

		return $this->withHeader('Content-Type', $contentType[0]);
	}

	/**
	 * addContentTypeToHeader
	 *
	 * @param   array  $headers
	 * @param   string $contentType
	 *
	 * @return array
	 */
	protected function addContentTypeToHeader($headers, $contentType)
	{
		$keys = array_change_key_case(array_keys($headers), CASE_LOWER);

		if (!isset($keys['content-type']))
		{
			$headers['content-type'] = array($contentType);
		}

		return $headers;
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
