<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

/**
 * The XmlResponse class.
 *
 * @since  {DEPLOY_VERSION}
 */
class XmlResponse extends TextResponse
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'application/xml';

	/**
	 * HtmlResponse constructor.
	 */
	public function __construct($xml = '', $status = 200, array $headers = array())
	{
		parent::__construct(
			$this->toString($xml),
			$status,
			$headers
		);
	}

	/**
	 * encode
	 *
	 * @param mixed $data
	 *
	 * @return  string
	 */
	protected function toString($data)
	{
		if ($data instanceof \SimpleXMLElement)
		{
			return $data->asXML();
		}
		elseif ($data instanceof \DOMDocument)
		{
			return $data->saveXML();
		}
		elseif (is_string($data))
		{
			return $data;
		}

		throw new \InvalidArgumentException(sprintf(
			'Invalid XML content type, %s provided.',
			gettype($data)
		));
	}
}
