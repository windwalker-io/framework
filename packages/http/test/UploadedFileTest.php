<?php

declare(strict_types=1);

namespace Windwalker\Http\Test;

use PHPUnit\Framework\Attributes\After;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Http\UploadedFile;
use Windwalker\Stream\Stream;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * Test class of UploadedFile
 *
 * @since 2.1
 */
class UploadedFileTest extends TestCase
{
    use BaseAssertionTrait;

    protected mixed $tmpFile = null;

    /** @var string[] */
    protected array $sourceFiles = [];

    #[After]
    protected function removeMovedTmpFile(): void
    {
        foreach ($this->sourceFiles as $file) {
            @unlink($file);
        }

        $this->sourceFiles = [];

        if (is_scalar($this->tmpFile) && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    #[Test]
    public function getStreamReturnsProvidedStream(): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $upload = new UploadedFile($stream);

        self::assertSame($stream, $upload->getStream());
    }

    #[Test]
    public function constructWrapsResourceIntoStream(): void
    {
        $upload = new UploadedFile(fopen('php://temp', 'wb+'));

        self::assertInstanceOf(Stream::class, $upload->getStream());
    }

    #[Test]
    public function getStreamReadsContentsFromFilePath(): void
    {
        $file = $this->createUploadSource('Foo');

        $upload = new UploadedFile($file);

        self::assertSame('Foo', (string) $upload->getStream());
    }

    #[Test]
    public function getStreamThrowsAfterFileMoved(): void
    {
        $upload = new UploadedFile($this->createUploadSource('Foo'));
        $this->tmpFile = $to = $this->createTargetPath();

        $upload->moveTo($to);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('already moved');

        $upload->getStream();
    }

    #[Test]
    public function moveToThrowsWhenFileAlreadyMoved(): void
    {
        $upload = new UploadedFile($this->createUploadSource('Foo'));
        $this->tmpFile = $to = $this->createTargetPath();

        $upload->moveTo($to);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('already moved');

        $upload->moveTo($to . '-2');
    }

    #[Test]
    public function moveToThrowsOnEmptyTargetPath(): void
    {
        $upload = new UploadedFile(new Stream('php://temp', 'wb+'));

        $this->expectException(InvalidArgumentException::class);

        $upload->moveTo('');
    }

    #[Test]
    public function moveToThrowsWhenTargetPathIsNotWritable(): void
    {
        $upload = new UploadedFile(new Stream('php://temp', 'wb+'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Unable to write to path');

        @$upload->moveTo(sys_get_temp_dir() . '/windwalker-no-such-dir-' . uniqid() . '/file');
    }

    #[Test]
    public function constructThrowsWhenNoValidFileOrStreamProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UploadedFile(null);
    }

    #[Test]
    public function constructAllowsAnyFileWhenUploadHasError(): void
    {
        $upload = new UploadedFile(null, 0, UPLOAD_ERR_NO_FILE);

        self::assertSame(UPLOAD_ERR_NO_FILE, $upload->getError());
    }

    #[Test]
    #[DataProvider('invalidErrorCodeProvider')]
    public function constructThrowsOnInvalidErrorCode(int $error): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UploadedFile(new Stream('php://temp', 'wb+'), 0, $error);
    }

    public static function invalidErrorCodeProvider(): array
    {
        return [
            'negative' => [-1],
            'too large' => [9],
        ];
    }

    #[Test]
    public function jsonSerializeReturnsFileInformation(): void
    {
        $upload = new UploadedFile('php://temp', 12, UPLOAD_ERR_OK, 'a.txt', 'text/plain');
        $upload->setFullPath('dir/a.txt');

        self::assertSame(
            [
                'name' => 'a.txt',
                'type' => 'text/plain',
                'tmp_name' => 'php://temp',
                'error' => UPLOAD_ERR_OK,
                'size' => 12,
                'full_path' => 'dir/a.txt',
            ],
            $upload->jsonSerialize()
        );
    }

    #[Test]
    public function jsonSerializeIsUsedByJsonEncode(): void
    {
        $upload = new UploadedFile('php://temp', 1, UPLOAD_ERR_OK, 'a.txt');

        $data = json_decode(json_encode($upload), true);

        self::assertSame('a.txt', $data['name']);
        self::assertNull($data['full_path']);
    }

    protected function createUploadSource(string $contents): string
    {
        $file = sys_get_temp_dir() . '/upload-' . uniqid();
        file_put_contents($file, $contents);
        $this->sourceFiles[] = $file;

        return $file;
    }

    protected function createTargetPath(): string
    {
        return sys_get_temp_dir() . '/windwalker-' . uniqid();
    }

    #[Test]
    public function moveToWritesStreamContentsToTargetPath(): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $to = sys_get_temp_dir() . '/windwalker-' . uniqid();

        self::assertFalse(is_file($to));

        $upload->moveTo($to);

        self::assertTrue(is_file($to));

        $contents = file_get_contents($to);

        self::assertEquals($stream->__toString(), $contents);
    }

    #[Test]
    public function moveToMovesUploadedFileFromPathToTargetPath(): void
    {
        $uploadFile = sys_get_temp_dir() . '/upload-' . uniqid();
        file_put_contents($uploadFile, 'Foo bar!');
        $upload = new UploadedFile($uploadFile, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $to = sys_get_temp_dir() . '/windwalker-' . uniqid();

        self::assertFalse(is_file($to));

        $upload->moveTo($to);

        self::assertTrue(is_file($to));

        $contents = file_get_contents($to);

        self::assertEquals('Foo bar!', $contents);

        @unlink($uploadFile);
    }

    #[Test]
    public function moveToWritesStreamContentsToTargetPathInNonCliSapi(): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $upload->setSapi('cgi');

        $this->tmpFile = $to = sys_get_temp_dir() . '/windwalker-' . uniqid();

        self::assertFalse(is_file($to));

        $upload->moveTo($to);

        self::assertTrue(is_file($to));

        $contents = file_get_contents($to);

        self::assertEquals($stream->__toString(), $contents);
    }

    #[Test]
    public function moveToThrowsForNonUploadedFileInNonCliSapi(): void
    {
        $uploadFile = sys_get_temp_dir() . '/upload-' . uniqid();
        file_put_contents($uploadFile, 'Foo bar!');
        $upload = new UploadedFile($uploadFile, 0, UPLOAD_ERR_OK);
        $upload->setSapi('cgi');

        $this->tmpFile = $to = sys_get_temp_dir() . '/windwalker-' . uniqid();

        self::assertFalse(is_file($to));

        try {
            self::assertExpectedException(
                function () use ($upload, $to) {
                    $upload->moveTo($to);
                },
                'RuntimeException',
                'Error moving uploaded file'
            );
        } finally {
            @unlink($uploadFile);
        }
    }

    #[Test]
    public function gettersReturnConstructorValues(): void
    {
        $upload = new UploadedFile(
            new Stream('php://temp', 'wb+'),
            123,
            UPLOAD_ERR_OK,
            'foo.png',
            'image/png'
        );

        self::assertSame(123, $upload->getSize());
        self::assertSame(UPLOAD_ERR_OK, $upload->getError());
        self::assertSame('foo.png', $upload->getClientFilename());
        self::assertSame('image/png', $upload->getClientMediaType());
    }
}
