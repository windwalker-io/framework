<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http\Helper;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Stream\Stream;

/**
 * The StreamHelper class.
 * 
 * @since  2.1
 */
abstract class StreamHelper
{
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
		$srcStream = $src instanceof StreamInterface ? $src : new Stream($src, Stream::MODE_READ_WRITE_RESET);

		static::copy($srcStream, $dest);

		$srcStream->close();
	}
}
