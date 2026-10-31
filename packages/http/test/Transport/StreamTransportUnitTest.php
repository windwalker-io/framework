<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Response\HttpClientResponse;
use Windwalker\Http\Transport\Options\StreamOptions;
use Windwalker\Http\Transport\StreamTransport;

class StreamTransportUnitTest extends TestCase
{
    protected function caOptions(StreamTransport $transport, StreamOptions|array $options): array
    {
        $ref = new \ReflectionMethod($transport, 'setCABundleToOptions');

        return $ref->invoke($transport, [], StreamOptions::wrap($options));
    }

    #[Test]
    public function toResponseSetsStatusBodyAndHeaders(): void
    {
        $res = (new StreamTransport())->toResponse(
            ['HTTP/1.1 202 Accepted', 'X-Foo: bar'],
            'payload'
        );

        self::assertSame(202, $res->getStatusCode());
        self::assertSame('bar', $res->getHeaderLine('X-Foo'));
        self::assertSame('payload', (string) $res->getBody());
    }

    #[Test]
    public function toResponseThrowsWhenStatusLineMissing(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        (new StreamTransport())->toResponse(['not a status line'], '');
    }

    #[Test]
    public function toResponseAllowsMissingStatusWhenRequested(): void
    {
        $res = (new StreamTransport())->toResponse([], 'x', new HttpClientResponse(), true);

        self::assertSame('x', (string) $res->getBody());
    }

    #[Test]
    public function setCABundleToOptionsUsesCertPathFileAsCafile(): void
    {
        $file = __FILE__;

        $context = $this->caOptions(new StreamTransport(), ['certpath' => $file]);

        self::assertSame($file, $context['ssl']['cafile']);
        self::assertArrayNotHasKey('capath', $context['ssl']);
    }

    #[Test]
    public function setCABundleToOptionsUsesCertPathDirectoryAsCapath(): void
    {
        $context = $this->caOptions(new StreamTransport(), ['certpath' => __DIR__]);

        self::assertSame(__DIR__, $context['ssl']['capath']);
    }

    #[Test]
    public function setCABundleToOptionsFallsBackToDetectedBundle(): void
    {
        $transport = new class () extends StreamTransport {
            protected function findCAPathOrFile(): ?string
            {
                return __FILE__;
            }
        };

        self::assertSame(__FILE__, $this->caOptions($transport, [])['ssl']['cafile']);
    }

    #[Test]
    public function setCABundleToOptionsLeavesContextUntouchedWhenNoBundleFound(): void
    {
        $transport = new class () extends StreamTransport {
            protected function findCAPathOrFile(): ?string
            {
                return null;
            }
        };

        self::assertSame([], $this->caOptions($transport, []));
    }

    #[Test]
    public function downloadThrowsWhenDestinationIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new StreamTransport())->download(new Request(), '');
    }

    #[Test]
    public function isSupportedReflectsAllowUrlFopen(): void
    {
        self::assertSame((bool) ini_get('allow_url_fopen'), (bool) StreamTransport::isSupported());
    }
}
