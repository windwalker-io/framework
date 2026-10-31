<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\File;

use CURLFile;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\File\HttpUploadFile;

class HttpUploadFileTest extends TestCase
{
    #[Before]
    protected function setUpVirtualFilesystem(): void
    {
        vfsStream::setup('root', null, ['hello.txt' => 'Hello World']);
    }

    #[Test]
    public function getMimeTypeReturnsExplicitMimeWithoutDetection(): void
    {
        $file = new HttpUploadFile('vfs://root/hello.txt', 'application/x-custom');

        self::assertSame('application/x-custom', $file->getMimeType());
    }

    #[Test]
    public function getMimeTypeDetectsFromFileWhenNotGiven(): void
    {
        $file = new HttpUploadFile('vfs://root/hello.txt');

        self::assertSame('text/plain', $file->getMimeType());
    }

    #[Test]
    public function getPostFilenameFallsBackToBasename(): void
    {
        $file = new HttpUploadFile('vfs://root/hello.txt');

        self::assertSame('hello.txt', $file->getPostFilename());

        $file->setPostFilename('renamed.txt');

        self::assertSame('renamed.txt', $file->getPostFilename());
    }

    #[Test]
    public function getContentReadsFileContents(): void
    {
        $file = new HttpUploadFile('vfs://root/hello.txt');

        self::assertSame('Hello World', $file->getContent());
    }

    #[Test]
    public function toCurlFileCarriesFilenameMimeAndPostname(): void
    {
        $file = new HttpUploadFile('vfs://root/hello.txt', 'text/x-foo', 'post.txt');

        $curl = $file->toCurlFile();

        self::assertInstanceOf(CURLFile::class, $curl);
        self::assertSame('vfs://root/hello.txt', $curl->getFilename());
        self::assertSame('text/x-foo', $curl->getMimeType());
        self::assertSame('post.txt', $curl->getPostFilename());
    }

    #[Test]
    public function toCurlFileUsesDetectedMimeAndBasename(): void
    {
        $curl = (new HttpUploadFile('vfs://root/hello.txt'))->toCurlFile();

        self::assertSame('text/plain', $curl->getMimeType());
        self::assertSame('hello.txt', $curl->getPostFilename());
    }
}
