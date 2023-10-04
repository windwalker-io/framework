<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\File;

/**
 * The HttpUploadFile class.
 */
class HttpUploadFile implements HttpUploadFileInterface
{
    public function __construct(
        protected string $filename,
        protected ?string $mimeType = null,
        protected ?string $postname = null
    ) {
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param  string  $filename
     *
     * @return  static  Return self to support chaining.
     */
    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): ?string
    {
        $mime = $this->mimeType;

        if (!$mime) {
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($this->filename);
            }
        }

        return $mime;
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
        return $this->postname ?: basename($this->getFilename());
    }

    /**
     * @param  string|null  $postname
     *
     * @return  static  Return self to support chaining.
     */
    public function setPostFilename(?string $postname): static
    {
        $this->postname = $postname;

        return $this;
    }

    public function toCurlFile(): \CURLFile
    {
        return new \CURLFile(
            $this->getFilename(),
            $this->getMimeType() ?: null,
            $this->getPostFilename() ?: null
        );
    }

    public function getContent(): string
    {
        return file_get_contents($this->filename);
    }
}
