<?php

declare(strict_types=1);

namespace Windwalker\Http\File;

use Unicorn\Flysystem\Base64DataUri;

/**
 * The HttpUploadFile class.
 */
class HttpUploadBase64 implements HttpUploadFileInterface
{
    protected string $data = '';

    protected string $base64 = '';

    public function __construct(
        string $base64,
        protected ?string $mimeType = null,
        protected ?string $postname = null
    ) {
        if (PHP_VERSION_ID < 80100) {
            throw new \LogicException(static::class . ' must use after PHP 8.1 or higher.');
        }

        $this->setBase64($base64);
    }

    /**
     * @return string
     */
    public function getBase64(): string
    {
        return $this->base64;
    }

    /**
     * @param  string  $base64
     *
     * @return  static  Return self to support chaining.
     */
    public function setBase64(string $base64): static
    {
        preg_match('/data:(\w+\/\w+);base64,(.*)/', $base64, $matches);

        $mime = (string) $matches[1];
        $data = (string) $matches[2];

        $this->base64 = $base64;
        $this->data = $data;

        $this->setMimeType($mime);

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
            $this->getBase64(),
            $this->getMimeType() ?: null,
            $this->getPostFilename() ?: null
        );
    }

    public function getContent(): string
    {
        return $this->base64;
    }

    protected static function decode(string $base64, ?string &$mime = null): string
    {
        preg_match('/data:(\w+\/\w+);base64,(.*)/', $base64, $matches);

        $mime = $matches[1];
        $code = $matches[2];

        return base64_decode($code);
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
}
