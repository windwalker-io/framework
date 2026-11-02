<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\HttpClient;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Http\Transport\MultiCurlTransport;
use Windwalker\Promise\Promise;
use Windwalker\Uri\Uri;

class MultiCurlTransportTest extends TestCase
{
    use TestServerExistsTrait;

    #[BeforeClass]
    public static function ensureTestServerIsRunning(): void
    {
        self::checkTestServerRunningOrSkip();
    }

    protected function createRequest(string $url): Request
    {
        return new Request()->withUri(new Uri($url))->withMethod('GET');
    }

    #[Test]
    public function sendRequestResolvesEachRequestWithItsOwnResponse(): void
    {
        $http = new HttpClient();

        [$r1, $r2] = Promise::all(
            [
                $http->getAsync(WINDWALKER_TEST_HTTP_URL . '/json?a=1'),
                $http->getAsync(WINDWALKER_TEST_HTTP_URL . '/json?a=2'),
            ],
        )->wait();

        self::assertSame('{"a":"1"}', $r1->getContent());
        self::assertSame('{"a":"2"}', $r2->getContent());
    }

    #[Test]
    public function downloadIsNotSupportedAndReturnsNull(): void
    {
        $transport = new MultiCurlTransport([], new CurlTransport());

        self::assertNull($transport->download(new Request(), 'dest'));
        self::assertTrue(MultiCurlTransport::isSupported());
    }
}
