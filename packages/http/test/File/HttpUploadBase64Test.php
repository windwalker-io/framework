<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\File;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\File\HttpUploadBase64;

class HttpUploadBase64Test extends TestCase
{
    private const string URI = 'data:text/plain;base64,SGVsbG8=';

    #[Test]
    public function constructorExtractsMimeTypeAndPayloadFromDataUri(): void
    {
        $file = new HttpUploadBase64(self::URI);

        self::assertSame('text/plain', $file->getMimeType());
        self::assertSame('SGVsbG8=', $file->getData());
        self::assertSame(self::URI, $file->getBase64());
    }

    #[Test]
    public function dataUriMimeOverridesConstructorMime(): void
    {
        $file = new HttpUploadBase64(self::URI, 'image/png', 'a.txt');

        self::assertSame('text/plain', $file->getMimeType());
        self::assertSame('a.txt', $file->getPostFilename());
    }

    #[Test]
    public function setBase64ReplacesMimeAndPayload(): void
    {
        $file = new HttpUploadBase64(self::URI);
        $file->setBase64('data:image/png;base64,AAAA');

        self::assertSame('image/png', $file->getMimeType());
        self::assertSame('AAAA', $file->getData());
    }

    #[Test]
    public function getContentReturnsDecodedBinary(): void
    {
        self::assertSame('Hello', (new HttpUploadBase64(self::URI))->getContent());
    }

    #[Test]
    public function toCurlFileCarriesMimeAndPostname(): void
    {
        $curl = (new HttpUploadBase64(self::URI, null, 'a.txt'))->toCurlFile();

        self::assertSame('Hello', $curl->data);
        self::assertSame('text/plain', $curl->mime);
        self::assertSame('a.txt', $curl->postname);
    }
}
