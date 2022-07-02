<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\File;

use Psr\Http\Message\StreamInterface;
use Windwalker\Stream\Stream;

/**
 * The HttpUploadFile class.
 */
class HttpUploadStream implements HttpUploadFileInterface
{
    /**
     * @param  mixed        $stream
     * @param  string|null  $mimeType
     * @param  string|null  $postname
     */
    public function __construct(
        mixed $stream,
        protected ?string $mimeType = null,
        protected ?string $postname = null
    ) {
        if (PHP_VERSION_ID < 80100) {
            throw new \LogicException(static::class . ' must use after PHP 8.1 or higher.');
        }

        $this->setStream(Stream::wrap($stream, Stream::MODE_READ_ONLY_FROM_BEGIN));
    }

    /**
     * @return StreamInterface
     */
    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * @param  StreamInterface  $stream
     *
     * @return  static  Return self to support chaining.
     */
    public function setStream(StreamInterface $stream): static
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param  string  $mimeType
     *
     * @return  static  Return self to support chaining.
     */
    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostFilename(): ?string
    {
        return $this->postname;
    }

    /**
     * @param  string  $postname
     *
     * @return  static  Return self to support chaining.
     */
    public function setPostFilename(?string $postname): static
    {
        $this->postname = $postname;

        return $this;
    }

    public function toCurlFile(): \CURLStringFile
    {
        return new \CURLStringFile(
            $this->getStream()->getContents(),
            $this->getMimeType() ?: null,
            $this->getPostFilename() ?: null
        );
    }

    public function getContent(): string
    {
        return $this->getStream()->getContents();
    }
}
