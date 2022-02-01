<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Stream;

use Psr\Http\Message\StreamInterface;

/**
 * The CachingStream class.
 *
 * This is a forked version from GuzzleHttp/Stream
 */
class CachingStream extends AbstractDecoratorStream
{
    /**
     * Stream being wrapped
     *
     * @var StreamInterface
     */
    private StreamInterface $remoteStream;

    /**
     * Number of bytes to skip reading due to a write on the buffer
     *
     * @var int
     */
    private int $skipReadBytes = 0;

    /**
     * We will treat the buffer object as the body of the stream
     *
     * @param StreamInterface|string|resource  $targetStream  Stream to cache
     * @param StreamInterface                  $cacheStream   Optionally specify where data is cached
     */
    public function __construct(
        mixed $targetStream,
        StreamInterface $cacheStream = null
    ) {
        $this->remoteStream = Stream::wrap($targetStream);

        parent::__construct($cacheStream ?: new Stream('php://temp', 'rb+'));
    }

    public function getSize(): ?int
    {
        return max($this->stream->getSize(), $this->remoteStream->getSize());
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if ($whence === SEEK_SET) {
            $byte = $offset;
        } elseif ($whence === SEEK_CUR) {
            $byte = $offset + $this->tell();
        } elseif ($whence === SEEK_END) {
            $size = $this->remoteStream->getSize();
            if ($size === null) {
                $size = $this->cacheEntireStream();
            }
            // Because 0 is the first byte, we seek to size - 1.
            $byte = $size - 1 - $offset;
        } else {
            throw new \InvalidArgumentException('Invalid whence');
        }

        $diff = $byte - $this->stream->getSize();

        if ($diff > 0) {
            // If the seek byte is greater the number of read bytes, then read
            // the difference of bytes to cache the bytes and inherently seek.
            $this->read($diff);
        } else {
            // We can just do a normal seek since we've already seen this byte.
            $this->stream->seek($byte);
        }
    }

    public function read($length): string
    {
        // Perform a regular read on any previously read data from the buffer
        $data = $this->stream->read($length);
        $remaining = $length - strlen($data);

        // More data was requested so read from the remote stream
        if ($remaining) {
            // If data was written to the buffer in a position that would have
            // been filled from the remote stream, then we must skip bytes on
            // the remote stream to emulate overwriting bytes from that
            // position. This mimics the behavior of other PHP stream wrappers.
            $remoteData = $this->remoteStream->read(
                $remaining + $this->skipReadBytes
            );

            if ($this->skipReadBytes) {
                $len = strlen($remoteData);
                $remoteData = substr($remoteData, $this->skipReadBytes);
                $this->skipReadBytes = max(0, $this->skipReadBytes - $len);
            }

            $data .= $remoteData;
            $this->stream->write($remoteData);
        }

        return $data;
    }

    public function write($string): int
    {
        // When appending to the end of the currently read stream, you'll want
        // to skip bytes from being read from the remote stream to emulate
        // other stream wrappers. Basically replacing bytes of data of a fixed
        // length.
        $overflow = (strlen($string) + $this->tell()) - $this->remoteStream->tell();
        if ($overflow > 0) {
            $this->skipReadBytes += $overflow;
        }

        return $this->stream->write($string);
    }

    public function eof(): bool
    {
        return $this->stream->eof() && $this->remoteStream->eof();
    }

    /**
     * Close both the remote stream and buffer stream
     */
    public function close(): void
    {
        $this->remoteStream->close();
        $this->stream->close();
    }

    private function cacheEntireStream(): int
    {
        $tmp = new NullStream();
        StreamHelper::copy($this, $tmp);

        return $this->tell();
    }
}
