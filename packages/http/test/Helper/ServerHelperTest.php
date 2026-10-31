<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Helper;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Helper\ServerHelper;
use Windwalker\Http\UploadedFile;

class ServerHelperTest extends TestCase
{
    private array $originalServer = [];

    #[Before]
    public function backupServer(): void
    {
        $this->originalServer = $_SERVER;
    }

    #[After]
    public function restoreServer(): void
    {
        $_SERVER = $this->originalServer;
    }

    #[Test]
    public function getValueMatchesExactThenCaseInsensitiveThenDefault(): void
    {
        $servers = ['REQUEST_METHOD' => 'POST', 'Host' => 'example.com'];

        self::assertSame('POST', ServerHelper::getValue($servers, 'REQUEST_METHOD'));
        self::assertSame('POST', ServerHelper::getValue($servers, 'request_method'));
        self::assertSame('example.com', ServerHelper::getValue($servers, 'HOST'));
        self::assertSame('d', ServerHelper::getValue($servers, 'missing', 'd'));
        self::assertNull(ServerHelper::getValue($servers, 'missing'));
    }

    #[Test]
    public function validateUploadedFilesAcceptsFlatAndNestedFiles(): void
    {
        $file = new UploadedFile('php://memory', 0, UPLOAD_ERR_OK);

        self::assertTrue(ServerHelper::validateUploadedFiles([]));
        self::assertTrue(ServerHelper::validateUploadedFiles(['a' => $file, 'b' => ['c' => $file]]));
    }

    #[Test]
    public function validateUploadedFilesRejectsInvalidEntries(): void
    {
        $file = new UploadedFile('php://memory', 0, UPLOAD_ERR_OK);

        self::assertFalse(ServerHelper::validateUploadedFiles(['a' => $file, 'b' => 'nope']));
    }

    #[Test]
    public function validateUploadedFilesRejectsInvalidEntriesInNestedArrays(): void
    {
        $file = new UploadedFile('php://memory', 0, UPLOAD_ERR_OK);

        self::assertFalse(ServerHelper::validateUploadedFiles(['a' => ['b' => $file, 'c' => 'nope']]));
    }

    #[Test]
    public function getAllHeadersReturnsArray(): void
    {
        self::assertIsArray(ServerHelper::getAllHeaders());
    }

    #[Test]
    public function apacheRequestHeadersReturnsArray(): void
    {
        self::assertIsArray(ServerHelper::apacheRequestHeaders());
    }
}
