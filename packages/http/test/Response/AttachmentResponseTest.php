<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Response\AttachmentResponse;
use Windwalker\Stream\Stream;

/**
 * Test class of AttachmentResponse
 */
class AttachmentResponseTest extends TestCase
{
    protected vfsStreamDirectory $root;

    #[Before]
    protected function setUpVirtualFilesystem(): void
    {
        $this->root = vfsStream::setup('root', null, ['file.txt' => 'file content']);
    }

    #[Test]
    public function constructorSetsDownloadDefaultHeaders(): void
    {
        $res = new AttachmentResponse();

        self::assertSame('application/octet-stream', $res->getHeaderLine('Content-Type'));
        self::assertSame('no-store, no-cache, must-revalidate', $res->getHeaderLine('Cache-Control'));
        self::assertSame('binary', $res->getHeaderLine('Content-Transfer-Encoding'));
        self::assertSame('none', $res->getHeaderLine('Content-Encoding'));
    }

    #[Test]
    public function constructorKeepsCustomHeadersAndStatus(): void
    {
        $res = new AttachmentResponse('php://temp', 201, ['Content-Type' => 'text/csv']);

        self::assertSame(201, $res->getStatusCode());
        self::assertSame('text/csv', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function constructorUsesStreamInstanceAsBody(): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $res = new AttachmentResponse($stream);

        self::assertSame($stream, $res->getBody());
    }

    #[Test]
    public function withContentTypeSetsHeader(): void
    {
        $res = new AttachmentResponse()->withContentType('image/png');

        self::assertSame('image/png', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function withFileLoadsBodyAndContentLength(): void
    {
        $res = new AttachmentResponse()->withFile($this->root->url() . '/file.txt', 'text/plain');

        self::assertSame('file content', (string) $res->getBody());
        self::assertSame('12', $res->getHeaderLine('Content-Length'));
        self::assertSame('text/plain', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function withFileKeepsContentTypeWhenNotProvided(): void
    {
        $res = new AttachmentResponse()->withFile($this->root->url() . '/file.txt');

        self::assertSame('application/octet-stream', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function withFileThrowsWhenFileMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('not exists');

        new AttachmentResponse()->withFile($this->root->url() . '/missing.txt');
    }

    #[Test]
    public function withFileDataWritesStringIntoBody(): void
    {
        $res = new AttachmentResponse()->withFileData('hello', 'text/plain');

        self::assertSame('hello', (string) $res->getBody());
        self::assertSame('5', $res->getHeaderLine('Content-Length'));
        self::assertSame('text/plain', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function withFileDataDelegatesStreamToWithFileStream(): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write('abc');

        $res = new AttachmentResponse()->withFileData($stream);

        self::assertSame('abc', (string) $res->getBody());
        self::assertSame('3', $res->getHeaderLine('Content-Length'));
    }

    #[Test]
    public function withFileStreamAcceptsResource(): void
    {
        $fp = fopen($this->root->url() . '/file.txt', 'rb');

        $res = new AttachmentResponse()->withFileStream($fp, 'text/plain');

        self::assertSame('file content', (string) $res->getBody());
        self::assertSame('12', $res->getHeaderLine('Content-Length'));
        self::assertSame('text/plain', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function withFilenameSetsAttachmentDisposition(): void
    {
        $res = new AttachmentResponse()->withFilename('é.txt');

        self::assertSame(
            "attachment; filename*=utf-8''%C3%A9.txt",
            $res->getHeaderLine('Content-Disposition'),
        );
    }

    #[Test]
    public function withInlineFilenameReplacesDefaultOctetStreamType(): void
    {
        $res = new AttachmentResponse()->withInlineFilename('a.png', 'image/png');

        self::assertSame('image/png', $res->getHeaderLine('Content-Type'));
        self::assertSame("inline; filename*=utf-8''a.png", $res->getHeaderLine('Content-Disposition'));
    }

    #[Test]
    public function withInlineFilenameRemovesOctetStreamTypeWhenNoTypeGiven(): void
    {
        $res = new AttachmentResponse()->withInlineFilename('a.png');

        self::assertFalse($res->hasHeader('Content-Type'));
    }

    #[Test]
    public function withInlineFilenameKeepsExplicitNonOctetContentType(): void
    {
        $res = new AttachmentResponse('php://temp', 200, ['Content-Type' => 'application/pdf'])
            ->withInlineFilename('a.pdf', 'image/png');

        self::assertSame('application/pdf', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function withPreviewableUsesGivenFilename(): void
    {
        $res = new AttachmentResponse()->withPreviewable('doc.pdf');

        self::assertStringStartsWith('inline;', $res->getHeaderLine('Content-Disposition'));
        self::assertStringContainsString('doc.pdf', $res->getHeaderLine('Content-Disposition'));
    }

    #[Test]
    public function withPreviewableGeneratesFilenameWhenOmitted(): void
    {
        $res = new AttachmentResponse()->withPreviewable();

        self::assertMatchesRegularExpression(
            "/^inline; filename\*=utf-8''.+$/",
            $res->getHeaderLine('Content-Disposition'),
        );
    }
}
