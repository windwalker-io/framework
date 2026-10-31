<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\File;

use CURLStringFile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\File\HttpUploadStringFile;

class HttpUploadStringFileTest extends TestCase
{
    #[Test]
    public function getContentReturnsRawData(): void
    {
        $file = new HttpUploadStringFile('abc');

        self::assertSame('abc', $file->getContent());

        $file->setData('xyz');

        self::assertSame('xyz', $file->getContent());
    }

    #[Test]
    public function toCurlFileCarriesDataMimeAndPostname(): void
    {
        $curl = (new HttpUploadStringFile('abc', 'text/plain', 'a.txt'))->toCurlFile();

        self::assertInstanceOf(CURLStringFile::class, $curl);
        self::assertSame('abc', $curl->data);
        self::assertSame('text/plain', $curl->mime);
        self::assertSame('a.txt', $curl->postname);
    }

    #[Test]
    public function toCurlFileConvertsEmptyMetadataToDefaults(): void
    {
        $curl = (new HttpUploadStringFile('abc', '', ''))->toCurlFile();

        self::assertSame('application/octet-stream', $curl->mime);
        self::assertSame('', $curl->postname);
    }
}
