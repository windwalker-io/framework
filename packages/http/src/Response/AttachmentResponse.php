<?php

declare(strict_types=1);

namespace Windwalker\Http\Response;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Stream\Stream;

use function Windwalker\response;
use function Windwalker\uid;

use const Windwalker\Stream\READ_ONLY_FROM_BEGIN;
use const Windwalker\Stream\READ_WRITE_FROM_BEGIN;
use const Windwalker\Stream\READ_WRITE_RESET;

/**
 * The AttachmentResponse class.
 *
 * @since  3.2
 */
class AttachmentResponse extends Response
{
    /**
     * Constructor.
     *
     * @param  mixed   $body     The body data.
     * @param  int     $status   The status code.
     * @param  array   $headers  The custom headers.
     */
    public function __construct(mixed $body = 'php://temp', int $status = 200, array $headers = [])
    {
        if (!$body instanceof StreamInterface) {
            $body = $this->createStream($body);
        }

        $headers['Content-Type'] ??= 'application/octet-stream';
        $headers['Cache-Control'] ??= 'no-store, no-cache, must-revalidate';
        $headers['Content-Transfer-Encoding'] ??= 'binary';
        $headers['Content-Encoding'] ??= 'none';

        parent::__construct($body, $status, $headers);
    }

    public function withContentType(string $type): static
    {
        return $this->withContentTypeIfExists($type);
    }

    protected function withContentTypeIfExists(?string $type = null): static
    {
        if ($type !== null) {
            return $this->withHeader('Content-Type', $type);
        }

        return $this;
    }

    protected function createStream(mixed $body): Stream
    {
        return new Stream($body, READ_ONLY_FROM_BEGIN);
    }

    /**
     * withFile
     *
     * @param  string       $file
     * @param  string|null  $contentType
     *
     * @return  static
     */
    public function withFile(string $file, ?string $contentType = null): static
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException('File: ' . $file . ' not exists.');
        }

        return $this->withStreamBody($this->createStream(fopen($file, READ_ONLY_FROM_BEGIN)))
            ->withContentTypeIfExists($contentType);
    }

    /**
     * @param  string|resource|StreamInterface  $data
     * @param  string|null                      $contentType
     *
     * @return  static
     */
    public function withFileData(mixed $data, ?string $contentType = null): static
    {
        if ($data instanceof StreamInterface) {
            return $this->withFileStream($data);
        }

        $stream = new Stream('php://temp', READ_WRITE_RESET);

        $stream->write($data);
        $stream->rewind();

        return $this->withStreamBody($stream)->withContentTypeIfExists($contentType);
    }

    public function withFileStream(mixed $file, ?string $contentType = null): static
    {
        $stream = Stream::wrap($file, READ_ONLY_FROM_BEGIN);

        return $this->withStreamBody($stream)->withContentTypeIfExists($contentType);
    }

    /**
     * withFileStream
     *
     * @param  StreamInterface  $stream
     *
     * @return  static
     * @throws InvalidArgumentException
     */
    protected function withStreamBody(StreamInterface $stream): static
    {
        return $this->withBody($stream)->withHeader('Content-Length', (string) $stream->getSize());
    }

    /**
     * withFilename
     *
     * @param  string  $filename
     *
     * @return  static
     * @throws InvalidArgumentException
     */
    public function withFilename(string $filename): static
    {
        return $this->withHeader(
            'Content-Disposition',
            HeaderHelper::attachmentContentDisposition($filename)
        );
    }

    /**
     * withInlineFilename
     *
     * @param  string       $filename
     * @param  string|null  $contentType
     *
     * @return  static
     */
    public function withInlineFilename(string $filename, ?string $contentType = null): static
    {
        $new = clone $this;

        if (str_contains($this->getHeaderLine('Content-Type'), 'application/octet-stream')) {
            if ($contentType === null) {
                $new = $new->withoutHeader('Content-Type');
            } else {
                $new = $new->withHeader('Content-Type', $contentType);
            }
        }

        return $new->withHeader(
            'Content-Disposition',
            HeaderHelper::inlineContentDisposition($filename)
        );
    }

    public function withPreviewable(?string $filename = null): static
    {
        return $this->withInlineFilename($filename ?? uid());
    }
}
