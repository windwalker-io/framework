<?php

declare(strict_types=1);

namespace Windwalker\Http\File;

/**
 * The HttpUploadFile class.
 */
class HttpUploadStringFile implements HttpUploadFileInterface
{
    public function __construct(
        protected string $data,
        protected ?string $mimeType = null,
        protected ?string $postname = null
    ) {
        if (PHP_VERSION_ID < 80100) {
            throw new \LogicException(static::class . ' must use after PHP 8.1 or higher.');
        }
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param  string  $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData(string $data): static
    {
        $this->data = $data;

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
            $this->getData(),
            $this->getMimeType() ?: null,
            $this->getPostFilename() ?: null
        );
    }

    public function getContent(): string
    {
        return $this->data;
    }
}
