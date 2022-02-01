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
 */
class CachingStream extends AbstractDecoratorStream
{
    protected StreamInterface $targetStream;

    protected int $lastCursor = 0;

    /**
     * We will treat the buffer object as the body of the stream
     *
     * @param StreamInterface   $target  Stream to cache
     * @param ?StreamInterface  $cache   Optionally specify where data is cached
     */
    public function __construct(
        StreamInterface $target,
        StreamInterface $cache = null
    ) {
        $this->targetStream = $target;
        parent::__construct($cache ?: new Stream('php://temp', 'rb+'));
    }

    public function getSize(): ?int
    {
        return max($this->stream->getSize(), $this->targetStream->getSize());
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if ($whence === SEEK_SET) {
            $cursor = $offset;
        } elseif ($whence === SEEK_CUR) {
            $cursor = $offset + $this->tell();
        } elseif ($whence === SEEK_END) {
            $size = $this->targetStream->getSize();
            if ($size === null) {
                $size = $this->cacheEntireStream();
            }
            // Because 0 is the first byte, we seek to size - 1.
            $cursor = $size - 1 - $offset;
        } else {
            throw new \InvalidArgumentException('Invalid whence');
        }

        $diff = $cursor - $this->stream->getSize();

        if ($diff > 0) {
            // If the seek byte is greater the number of read bytes, then read
            // the difference of bytes to cache the bytes and inherently seek.
            $this->read($diff);
        } else {
            // We can just do a normal seek since we've already seen this byte.
            $this->stream->seek($cursor);
        }
    }

    public function read($length): string
    {
        $data = $this->stream->read($length);

        $remainLength = $length - strlen($data);

        // If the length that we want to read was more than cached,
        // We must read more from target stream and reset last cursor.
        if ($remainLength) {
            $readData = $this->targetStream->read(
                $remainLength + $this->lastCursor
            );

            if ($this->lastCursor > 0) {
                $len = strlen($readData);
                $readData = substr($readData, $this->lastCursor);
                $this->lastCursor = max(0, $this->lastCursor - $len);
            }

            $data .= $readData;
            $this->stream->write($readData);
        }

        return $data;
    }

    public function write($string): int
    {
        // If wrote string + exists string is longer than target stream,
        // We must plus the last cursor position.
        $overflow = strlen($string) + $this->tell() - $this->targetStream->tell();

        if ($overflow > 0) {
            $this->lastCursor += $overflow;
        }

        return $this->stream->write($string);
    }

    public function eof(): bool
    {
        return $this->stream->eof() && $this->targetStream->eof();
    }

    /**
     * Close both the remote stream and buffer stream
     */
    public function close(): void
    {
        $this->targetStream->close();
        $this->stream->close();
    }

    private function cacheEntireStream(): int
    {
        StreamHelper::copy($this->targetStream, $this->stream);

        return $this->tell();
    }
}
