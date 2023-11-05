<?php

declare(strict_types=1);

namespace Windwalker\Http\Response;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Windwalker\Stream\Stream;

/**
 * The TextResponse class.
 *
 * @since  3.0
 */
class TextResponse extends AbstractContentTypeResponse
{
    /**
     * Handle body to stream object.
     *
     * @param  string|resource|StreamInterface  $body  The body data.
     *
     * @return  StreamInterface  Converted to stream object.
     */
    protected function handleBody(mixed $body): StreamInterface
    {
        if (is_string($body) || is_resource($body)) {
            $stream = new Stream('php://temp', 'wb+');
            $stream->write($body);
            $stream->rewind();

            $body = $stream;
        }

        if (!$body instanceof StreamInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid body content type %s, please provide string or StreamInterface',
                    gettype($body)
                )
            );
        }

        return $body;
    }
}
