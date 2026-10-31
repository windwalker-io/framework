<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\File;

use CURLStringFile;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\File\HttpUploadStream;
use Windwalker\Stream\Stream;

class HttpUploadStreamTest extends TestCase
{
    private static function memory(string $data)
    {
        $resource = fopen('php://temp', 'wb+');
        fwrite($resource, $data);
        rewind($resource);

        return $resource;
    }

    #[Test]
    public function constructorWrapsPathAsStream(): void
    {
        vfsStream::setup('root', null, ['hello.txt' => 'Hello']);

        $file = new HttpUploadStream('vfs://root/hello.txt');

        self::assertInstanceOf(Stream::class, $file->getStream());
        self::assertSame('Hello', $file->getContent());
    }

    #[Test]
    public function constructorWrapsResourceAndReadsFromBeginning(): void
    {
        $resource = fopen('php://temp', 'wb+');
        fwrite($resource, 'Resource data');
        rewind($resource);

        $file = new HttpUploadStream($resource);

        self::assertSame('Resource data', $file->getContent());
    }

    #[Test]
    public function toCurlFileCarriesStreamContentsMimeAndPostname(): void
    {
        $curl = (new HttpUploadStream(self::memory('Hello'), 'text/plain', 'a.txt'))->toCurlFile();

        self::assertInstanceOf(CURLStringFile::class, $curl);
        self::assertSame('Hello', $curl->data);
        self::assertSame('text/plain', $curl->mime);
        self::assertSame('a.txt', $curl->postname);
    }

    #[Test]
    public function setStreamReplacesStream(): void
    {
        $file = new HttpUploadStream(self::memory('old'));
        $file->setStream(new Stream('php://memory', 'wb+'));

        self::assertSame('', $file->getContent());
    }
}
