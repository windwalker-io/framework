<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http\Stream;

use Psr\Http\Message\StreamInterface;

/**
 * The ArrayStream class.
 * 
 * @since  2.1
 */
class StringStream extends Stream implements StreamInterface
{
	/**
	 * Stream resource.
	 *
	 * @var string
	 */
	protected $resource;

	/**
	 * Property position.
	 *
	 * @var  integer
	 */
	protected $pointer = 0;

	/**
	 * Property pointer.
	 *
	 * @var  integer
	 */
	protected $readPosition = 0;

	/**
	 * Property metadata.
	 *
	 * @var  array
	 */
	private $metadata = array (
		'wrapper_type' => 'string',
		'stream_type'  => 'STDIO',
		'mode'         => 'r+b',
		'unread_bytes' => 0,
		'seekable'     => true,
		'uri'          => '',
		'timed_out'    => false,
		'blocked'      => true,
		'eof'          => false,
	);

	/**
	 * Property seekable.
	 *
	 * @var  boolean
	 */
	protected $seekable = true;

	/**
	 * Property writable.
	 *
	 * @var  boolean
	 */
	protected $writable = true;

	/**
	 * Class init.
	 *
	 * @param  string  $stream  The stream resource cursor.
	 * @param  string  $mode    Mode with which to open stream
	 */
	public function __construct($stream = '', $mode = 'rb+')
	{
		$this->attach($stream, $mode);
	}

	/**
	 * Closes the stream and any underlying resources.
	 *
	 * @return void
	 */
	public function close()
	{
		$this->detach();
	}

	/**
	 * Method to attach resource into object.
	 *
	 * @param   string|resource  $stream  The stream resource cursor.
	 * @param   string           $mode    Mode with which to open stream
	 *
	 * @return  static Return self to support chaining.
	 */
	public function attach($stream, $mode = 'rb+')
	{
		$this->stream = $stream;

		if (is_resource($stream))
		{
			throw new \InvalidArgumentException('StringStream do not support resource.');
		}

		if (is_array($stream) || (is_object($stream) && !is_callable(array($stream, '__toString'))))
		{
			throw new \InvalidArgumentException('StringStream only support string as resource.');
		}

		if (strpos('+', $mode) === false)
		{
			$this->writable = false;
		}

		$this->resource = (string) $stream;

		return $this;
	}

	/**
	 * Separates any underlying resources from the stream.
	 *
	 * After the stream has been detached, the stream is in an unusable state.
	 *
	 * @return resource|null Underlying PHP stream, if any
	 */
	public function detach()
	{
		$resource = $this->resource;

		$this->resource = null;
		$this->stream   = null;
		$this->pointer  = 0;
		$this->seekable = true;
		$this->writable = true;

		return $resource;
	}

	/**
	 * Get the size of the stream if known.
	 *
	 * @return int|null Returns the size in bytes if known, or null if unknown.
	 */
	public function getSize()
	{
		if ($this->resource === null)
		{
			return null;
		}

		return strlen($this->resource);
	}

	/**
	 * Returns the current position of the file read/write pointer
	 *
	 * @return int Position of the file pointer
	 * @throws \RuntimeException on error.
	 */
	public function tell()
	{
		if ($this->resource === null)
		{
			throw new \RuntimeException('No resource set.');
		}

		return $this->pointer;
	}

	/**
	 * Returns true if the stream is at the end of the stream.
	 *
	 * @return bool
	 */
	public function eof()
	{
		return $this->readPosition > $this->getSize();
	}

	/**
	 * Returns whether or not the stream is seekable.
	 *
	 * @return bool
	 */
	public function isSeekable()
	{
		return $this->seekable;
	}

	/**
	 * Seek to a position in the stream.
	 *
	 * @link http://www.php.net/manual/en/function.fseek.php
	 *
	 * @param int $offset Stream offset
	 * @param int $whence Specifies how the cursor position will be calculated
	 *                    based on the seek offset. Valid values are identical to the built-in
	 *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
	 *                    offset bytes SEEK_CUR: Set position to current location plus offset
	 *                    SEEK_END: Set position to end-of-stream plus offset.
	 *
	 * @return boolean
	 *
	 * @throws \RuntimeException on failure.
	 */
	public function seek($offset, $whence = SEEK_SET)
	{
		if (!$this->isSeekable())
		{
			throw new \RuntimeException('Stream is not seekable');
		}

		if ($whence == SEEK_SET)
		{
			$this->pointer = $offset;
		}
		elseif ($whence == SEEK_CUR)
		{
			$this->pointer += $offset;
		}
		elseif ($whence == SEEK_END)
		{
			$this->pointer = $this->getSize();
			$this->pointer += $offset;
		}

		if ($this->pointer < 0)
		{
			throw new \RuntimeException('Position should not less than 0.');
		}

		return true;
	}

	/**
	 * Seek to the beginning of the stream.
	 *
	 * If the stream is not seekable, this method will raise an exception;
	 * otherwise, it will perform a seek(0).
	 *
	 * @see  seek()
	 * @link http://www.php.net/manual/en/function.fseek.php
	 * @throws \RuntimeException on failure.
	 */
	public function rewind()
	{
		$this->readPosition = 0;

		return $this->seek(0);
	}

	/**
	 * Returns whether or not the stream is writable.
	 *
	 * @return bool
	 */
	public function isWritable()
	{
		return $this->writable;
	}

	/**
	 * Write data to the stream.
	 *
	 * @param string $string The string that is to be written.
	 *
	 * @return int Returns the number of bytes written to the stream.
	 * @throws \RuntimeException on failure.
	 */
	public function write($string)
	{
		$length = strlen($string);

		$start = substr($this->resource, 0, $this->pointer);
		$end   = substr($this->resource, $this->pointer + $length);

		$this->resource = $start . $string . $end;

		$this->pointer = strlen($start . $string);

		return $string;
	}

	/**
	 * Returns whether or not the stream is readable.
	 *
	 * @return bool
	 */
	public function isReadable()
	{
		return true;
	}

	/**
	 * Read data from the stream.
	 *
	 * @param int $length Read up to $length bytes from the object and return
	 *                    them. Fewer than $length bytes may be returned if underlying stream
	 *                    call returns fewer bytes.
	 *
	 * @return string Returns the data read from the stream, or an empty string
	 *     if no bytes are available.
	 * @throws \RuntimeException if an error occurs.
	 */
	public function read($length)
	{
		$this->readPosition = $this->pointer;
		
		$result = substr($this->resource, $this->readPosition, $length);

		$this->pointer += $length;
		$this->readPosition = $this->pointer;

		return $result;
	}

	/**
	 * Returns the remaining contents in a string
	 *
	 * @return string
	 * @throws \RuntimeException if unable to read or an error occurs while
	 *     reading.
	 */
	public function getContents()
	{
		if ($this->resource === null)
		{
			return '';
		}

		$result = substr($this->resource, $this->pointer);

		if ($result === false)
		{
			return '';
		}

		return $result;
	}

	/**
	 * Get stream metadata as an associative array or retrieve a specific key.
	 *
	 * The keys returned are identical to the keys returned from PHP's
	 * stream_get_meta_data() function.
	 *
	 * @link http://php.net/manual/en/function.stream-get-meta-data.php
	 *
	 * @param string $key Specific metadata to retrieve.
	 *
	 * @return array|mixed|null Returns an associative array if no key is
	 *     provided. Returns a specific key value if a key is provided and the
	 *     value is found, or null if the key is not found.
	 */
	public function getMetadata($key = null)
	{
		$metadata = $this->metadata;

		$metadata['eof'] = $this->eof();
		$metadata['seekable'] = $this->isSeekable();
		$metadata['unread_bytes'] = $this->getSize() - $this->pointer;
		$metadata['mode'] = 'rb';

		if ($this->isWritable())
		{
			$metadata['mode'] = 'r+b';
		}

		if ($key === null)
		{
			return $metadata;
		}

		if (!array_key_exists($key, $metadata))
		{
			return null;
		}

		return $metadata[$key];
	}

	/**
	 * Method to get property Resource
	 *
	 * @return  resource
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * Method to set property seekable
	 *
	 * @param   boolean $seekable
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function seekable($seekable)
	{
		$this->seekable = (boolean) $seekable;

		return $this;
	}

	/**
	 * Method to set property writable
	 *
	 * @param   boolean $writable
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function writable($writable)
	{
		$this->writable = (boolean) $writable;

		return $this;
	}
}
