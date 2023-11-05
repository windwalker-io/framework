<?php

declare(strict_types=1);

namespace Windwalker\Stream;

use Psr\Http\Message\StreamInterface;

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
     * @param  StreamInterface  $src   Source stream.
     * @param  StreamInterface  $dest  Target stream.
     *
     * @return  void
     */
    public static function copy(StreamInterface $src, StreamInterface $dest): void
    {
        if ($src->isSeekable()) {
            $src->rewind();
        }

        while (!$src->eof()) {
            $dest->write($src->read(4096));
        }
    }

    /**
     * Copy a stream to target resource.
     *
     * @param  StreamInterface  $src   The source stream to copy.
     * @param  mixed            $dest  The target stream, if is a path or resource, will auto create Stream object.
     *
     * @return  void
     */
    public static function copyTo(StreamInterface $src, mixed $dest): void
    {
        $destStream = Stream::wrap($dest, READ_WRITE_RESET);

        static::copy($src, $destStream);

        $destStream->close();
    }

    /**
     * Copy a stream to target resource.
     *
     * @param  mixed            $src   The source stream to copy, if is a path or resource, will auto create Stream
     *                                 object.
     * @param  StreamInterface  $dest  The target stream.
     *
     * @return  void
     */
    public static function copyFrom(mixed $src, StreamInterface $dest): void
    {
        $srcStream = $src instanceof StreamInterface ? $src : new Stream($src, READ_ONLY_FROM_BEGIN);

        static::copy($srcStream, $dest);

        $srcStream->close();
    }

    public static function copyToString(StreamInterface $stream, int $maxLen = -1): string
    {
        $buffer = '';

        if ($maxLen === -1) {
            while (!$stream->eof()) {
                $buf = $stream->read(1048576);
                if ($buf === '') {
                    break;
                }
                $buffer .= $buf;
            }
            return $buffer;
        }

        $len = 0;
        while (!$stream->eof() && $len < $maxLen) {
            $buf = $stream->read($maxLen - $len);
            if ($buf === '') {
                break;
            }
            $buffer .= $buf;
            $len = strlen($buffer);
        }

        return $buffer;
    }
}
