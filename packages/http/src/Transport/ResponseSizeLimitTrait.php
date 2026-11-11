<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Exception\HttpResponseSizeExceededException;
use Windwalker\Stream\Stream;
use Windwalker\Stream\StreamHelper;

use const Windwalker\Stream\READ_WRITE_RESET;

/**
 * Shared helpers for transports that must cap the downloaded response body
 * size to avoid buffering an unexpectedly huge response into memory.
 */
trait ResponseSizeLimitTrait
{
    /**
     * Throw once `$currentSize` exceeds `$maxBytes`. `$maxBytes` of `null` means no limit.
     *
     * @throws  HttpResponseSizeExceededException  If the body exceeds `$maxBytes`.
     */
    protected function assertResponseSizeWithinLimit(int $currentSize, ?int $maxBytes): void
    {
        if ($maxBytes !== null && $currentSize > $maxBytes) {
            throw new HttpResponseSizeExceededException(
                sprintf('Response body exceeds the configured max size of %d bytes.', $maxBytes),
            );
        }
    }

    /**
     * Read the stream contents into a string, aborting once it exceeds `$maxBytes`
     * to avoid buffering an unexpectedly huge response body into memory.
     *
     * Only suitable for "pull" based transports that read from a `StreamInterface`.
     *
     * @throws  HttpResponseSizeExceededException  If the body exceeds `$maxBytes`.
     */
    protected function readContentsWithLimit(StreamInterface $stream, ?int $maxBytes): string
    {
        if ($maxBytes === null) {
            return $stream->getContents();
        }

        $content = StreamHelper::copyToString($stream, $maxBytes + 1);

        $this->assertResponseSizeWithinLimit(strlen($content), $maxBytes);

        return $content;
    }

    /**
     * Copy the stream to `$dest` in chunks, aborting once the copied size exceeds
     * `$maxBytes` to avoid buffering an unexpectedly huge response body into memory.
     *
     * Only suitable for "pull" based transports that read from a `StreamInterface`.
     *
     * @throws  HttpResponseSizeExceededException  If the body exceeds `$maxBytes`.
     */
    protected function copyToWithLimit(StreamInterface $src, mixed $dest, ?int $maxBytes): void
    {
        if ($maxBytes === null) {
            StreamHelper::copyTo($src, $dest);

            return;
        }

        $destStream = Stream::wrap($dest, READ_WRITE_RESET);

        if ($src->isSeekable()) {
            $src->rewind();
        }

        $total = 0;

        try {
            while (!$src->eof()) {
                $buf = $src->read(4096);

                if ($buf === '') {
                    break;
                }

                $total += strlen($buf);

                $this->assertResponseSizeWithinLimit($total, $maxBytes);

                $destStream->write($buf);
            }
        } finally {
            $destStream->close();
        }
    }
}
