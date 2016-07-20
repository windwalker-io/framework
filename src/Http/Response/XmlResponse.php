<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

/**
 * The XmlResponse class.
 *
 * @since  3.0
 */
class XmlResponse extends TextResponse
{
	/**
	 * Content type.
	 *
	 * @var  string
	 */
	protected $type = 'application/xml';

	/**
	 * Constructor.
	 *
	 * @param  string  $xml      The XML body data.
	 * @param  int     $status   The status code.
	 * @param  array   $headers  The custom headers.
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
	 * Convert XML object to string.
	 *
	 * @param   \SimpleXMLElement|\DOMDocument|string  $data  XML object or data.
	 *
	 * @return  string  Converted XML string.
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
