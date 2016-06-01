<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http\Helper;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Output\StreamOutput;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Stream\Stream;

/**
 * The StreamHelper class.
 * 
 * @since  2.1
 */
abstract class StreamHelper
{
	/**
	 * Property outputClass for test use.
	 *
	 * @var  StreamOutput
	 */
	public static $outputObject;

	/**
	 * Copy stream to another stream.
	 *
	 * @param   StreamInterface  $src   Source stream.
	 * @param   StreamInterface  $dest  Target stream.
	 *
	 * @return  void
	 */
	public static function copy(StreamInterface $src, StreamInterface $dest)
	{
		if ($src->isSeekable())
		{
			$src->rewind();
		}

		while (!$src->eof())
		{
			$dest->write($src->read(4096));
		}
	}

	/**
	 * Copy a stream to target resource.
	 *
	 * @param   StreamInterface   $src   The source stream to copy.
	 * @param   string            $dest  The target stream, if is a path or resource, will auto create Stream object.
	 *
	 * @return  void
	 */
	public static function copyTo(StreamInterface $src, $dest)
	{
		$destStream = $dest instanceof StreamInterface ? $dest : new Stream($dest, Stream::MODE_READ_WRITE_RESET);

		static::copy($src, $destStream);

		$destStream->close();
	}

	/**
	 * Copy a stream to target resource.
	 *
	 * @param   string           $src   The source stream to copy, if is a path or resource, will auto create Stream object.
	 * @param   StreamInterface  $dest  The target stream.
	 *
	 * @return  void
	 */
	public static function copyFrom($src, StreamInterface $dest)
	{
		$srcStream = $src instanceof StreamInterface ? $src : new Stream($src, Stream::MODE_READ_ONLY_FROM_BEGIN);

		static::copy($srcStream, $dest);

		$srcStream->close();
	}

	/**
	 * A simple method to quickly send attachment stream download.
	 *
	 * @param   string|resource    $source    The file source, can be file path or resource.
	 * @param   ResponseInterface  $response  A custom Response object to contain your headers.
	 * @param   array              $options   Options to provide some settings, currently supports
	 *                                        "delay" and "filename".
	 *
	 * @return  void
	 */
	public static function sendAttachment($source, ResponseInterface $response = null, $options = array())
	{
		$stream = new Stream($source, 'r');

		/** @var MessageInterface|ResponseInterface $response */
		$response = $response ? : new Response;

		$filename = null;

		if (is_string($source))
		{
			$filename = pathinfo($source, PATHINFO_BASENAME);
		}

		if (isset($options['filename']))
		{
			$filename = $options['filename'];
		}

		$response = HeaderHelper::prepareAttachmentHeaders($response, $filename);

		$response = $response->withBody($stream);

		$output = static::$outputObject;

		if (!$output instanceof StreamOutput)
		{
			$output = new StreamOutput;
		}

		if (isset($options['delay']))
		{
			$output->setDelay($options['delay']);
		}

		$output->respond($response);
	}
}
